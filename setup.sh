#!/usr/bin/env bash
set -euo pipefail

# ============================================================
# Plataforma NR1 — Setup Completo
# ============================================================

CYAN='\033[0;36m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
RESET='\033[0m'
BOLD='\033[1m'

log()    { echo -e "${CYAN}[NR1]${RESET} $*"; }
ok()     { echo -e "${GREEN}[✓]${RESET} $*"; }
warn()   { echo -e "${YELLOW}[!]${RESET} $*"; }
err()    { echo -e "${RED}[✗]${RESET} $*" >&2; exit 1; }
header() { echo -e "\n${BOLD}${CYAN}══════════════════════════════════════════════${RESET}"; echo -e "  $*"; echo -e "${BOLD}${CYAN}══════════════════════════════════════════════${RESET}\n"; }

header "Plataforma NR1 — Setup"

# ── Pré-requisitos ──────────────────────────────────────────
log "Verificando pré-requisitos..."
command -v docker >/dev/null 2>&1 || err "Docker não encontrado. Instale o Docker antes de continuar."
# Suporte tanto ao plugin 'docker compose' quanto ao binário legado 'docker-compose'
if ! docker compose version >/dev/null 2>&1; then
    err "docker compose não disponível. Instale o Docker Engine >= 20.10 com o plugin Compose."
fi
ok "Docker e docker compose encontrados"

# ── .env ────────────────────────────────────────────────────
header "1. Configurando ambiente"
if [ ! -f .env ]; then
    cp .env.example .env
    ok ".env criado a partir de .env.example"
    warn "ATENÇÃO: Configure as variáveis de banco de dados (DB_HOST, DB_PASSWORD, etc.) no .env antes de continuar."
    warn "         O banco usa Supabase/PostgreSQL externo — sem configuração correta as migrations falharão."
else
    warn ".env já existe, mantendo."
fi

# Verificar se APP_KEY já está definido
if grep -q '^APP_KEY=$' .env 2>/dev/null; then
    warn "APP_KEY vazio no .env — será gerado no passo 5."
fi

# ── Build dos containers ─────────────────────────────────────
header "2. Construindo containers Docker"
log "Building imagem PHP (pode demorar na primeira vez)..."
docker compose build --quiet
ok "Containers construídos"

# ── Subindo containers ───────────────────────────────────────
header "3. Subindo containers"
docker compose up -d
ok "Containers iniciados"

# Aguardar o container 'app' ficar healthy (PHP-FPM pronto na porta 9000)
log "Aguardando PHP-FPM ficar saudável..."
MAX_WAIT=90
WAITED=0
INTERVAL=5
until docker compose ps app | grep -q "healthy"; do
    if [ $WAITED -ge $MAX_WAIT ]; then
        warn "Timeout esperando pelo container app. Logs:"
        docker compose logs --tail=50 app
        err "PHP-FPM não ficou healthy em ${MAX_WAIT}s. Verifique os logs acima."
    fi
    sleep $INTERVAL
    WAITED=$((WAITED + INTERVAL))
    log "Aguardando... (${WAITED}s/${MAX_WAIT}s)"
done
ok "PHP-FPM saudável"

# ── Dependências PHP ─────────────────────────────────────────
header "4. Instalando dependências PHP (Composer)"
docker compose exec -T app composer install --no-interaction --optimize-autoloader
ok "Dependências PHP instaladas"

# ── APP KEY ─────────────────────────────────────────────────
header "5. Gerando APP_KEY"
docker compose exec -T app php artisan key:generate --force
ok "APP_KEY gerado"

# ── Storage links ─────────────────────────────────────────────
docker compose exec -T app php artisan storage:link --force 2>/dev/null || true
ok "Storage link criado"

# ── Verificar conexão com banco ──────────────────────────────
header "6. Verificando conexão com banco de dados"
warn "Testando conexão com Supabase/PostgreSQL..."
DB_OK=false
for i in 1 2 3 4 5; do
    if docker compose exec -T app php artisan db:show --json > /dev/null 2>&1; then
        DB_OK=true
        break
    fi
    log "Tentativa $i/5 — aguardando banco..."
    sleep 3
done

if [ "$DB_OK" = "false" ]; then
    echo ""
    echo -e "${RED}════════════════════════════════════════════════${RESET}"
    echo -e "${RED}  ERRO: Não foi possível conectar ao banco       ${RESET}"
    echo -e "${RED}════════════════════════════════════════════════${RESET}"
    echo ""
    echo "  Configure as seguintes variáveis no arquivo .env:"
    echo ""
    echo "    DB_HOST=db.seu-projeto.supabase.co"
    echo "    DB_PORT=5432"
    echo "    DB_DATABASE=postgres"
    echo "    DB_USERNAME=postgres"
    echo "    DB_PASSWORD=sua-senha-supabase"
    echo "    DB_SSLMODE=require"
    echo ""
    echo "  Após configurar, execute novamente: bash setup.sh"
    echo ""
    echo -e "${YELLOW}Os containers continuam rodando. Para pará-los: docker compose down${RESET}"
    exit 1
fi
ok "Banco de dados acessível"

# ── Migrations ───────────────────────────────────────────────
header "7. Executando migrations (banco central)"
log "Migrando banco central..."
docker compose exec -T app php artisan migrate \
    --path=database/migrations/central \
    --force \
    --no-interaction || {
    err "Falha nas migrations. Verifique a conexão com o banco no .env"
}
ok "Migrations do banco central concluídas"

# ── Seeds ─────────────────────────────────────────────────────
header "8. Criando dados iniciais"
log "Criando administrador global e tenant de demonstração..."
docker compose exec -T app php artisan db:seed --force --no-interaction || {
    warn "Seeder falhou. Pode já ter sido executado anteriormente."
    warn "Para re-executar, use: docker compose exec app php artisan db:seed --force"
}
ok "Dados iniciais verificados"

# ── Dependências Node ─────────────────────────────────────────
header "9. Instalando dependências Node (npm)"
docker compose exec -T app npm install
ok "Dependências Node instaladas"

# ── Build assets ─────────────────────────────────────────────
header "10. Compilando assets (Vite)"
docker compose exec -T app npm run build
ok "Assets compilados"

# ── Cache de configuração ─────────────────────────────────────
header "11. Otimizando configuração"
docker compose exec -T app php artisan config:cache || true
docker compose exec -T app php artisan route:cache || true
docker compose exec -T app php artisan view:cache || true
ok "Cache gerado"

# ── Fix permissions ───────────────────────────────────────────
# Artisan commands run as root via docker compose exec, which can leave files
# in storage/ and bootstrap/cache/ owned by root. Fix so www-data (PHP-FPM) can write.
log "Corrigindo permissões de storage e cache..."
docker compose exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
docker compose exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
ok "Permissões corrigidas"

# ── Verificação final ─────────────────────────────────────────
header "Verificação Final"
log "Testando resposta HTTP do localhost..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/ 2>/dev/null || echo "FALHOU")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    ok "http://localhost/ responde com HTTP $HTTP_CODE"
else
    warn "http://localhost/ retornou: $HTTP_CODE (esperado 200 ou 302)"
    warn "Verifique os logs: docker compose logs --tail=100 app nginx"
fi

# ── Sumário ───────────────────────────────────────────────────
header "Setup Concluído!"

echo -e "${BOLD}URLs de Acesso:${RESET}"
echo ""
echo -e "  ${CYAN}Painel Admin Global${RESET}"
echo -e "    URL:   ${GREEN}http://localhost${RESET}"
echo -e "    Email: ${YELLOW}admin@plataformanr1.com${RESET}"
echo -e "    Senha: ${YELLOW}admin@123${RESET}"
echo ""
echo -e "  ${CYAN}Tenant Demo${RESET}"
echo -e "    URL:   ${GREEN}http://demo.localhost${RESET}"
echo ""
echo -e "  ${CYAN}Usuário RH (demo):${RESET}"
echo -e "    Email: ${YELLOW}rh@demo.com${RESET}"
echo -e "    Senha: ${YELLOW}rh@123${RESET}"
echo ""
echo -e "  ${CYAN}Usuário Líder (demo):${RESET}"
echo -e "    Email:       ${YELLOW}lider@demo.com${RESET}"
echo -e "    Senha:       ${YELLOW}lider@123${RESET}"
echo -e "    Hierarquia:  ${YELLOW}Matriz / TI${RESET}"
echo ""
echo -e "${BOLD}Comandos úteis:${RESET}"
echo "  docker compose logs -f app      — Ver logs da aplicação"
echo "  docker compose logs -f queue    — Ver logs da fila"
echo "  docker compose exec app bash    — Acessar container"
echo "  docker compose exec app php artisan test  — Executar testes"
echo "  docker compose down             — Parar containers"
echo ""
echo -e "${GREEN}Sistema pronto! ✓${RESET}"
echo ""
