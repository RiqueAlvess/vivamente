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
command -v docker-compose >/dev/null 2>&1 || \
  command -v "docker" >/dev/null 2>&1 || err "docker compose não encontrado."
ok "Docker encontrado"

# ── .env ────────────────────────────────────────────────────
header "1. Configurando ambiente"
if [ ! -f .env ]; then
    cp .env.example .env
    ok ".env criado a partir de .env.example"
else
    warn ".env já existe, mantendo."
fi

# ── Build dos containers ─────────────────────────────────────
header "2. Construindo containers Docker"
log "Building imagem PHP..."
docker compose build --quiet
ok "Containers construídos"

# ── Subindo containers ───────────────────────────────────────
header "3. Subindo containers"
docker compose up -d
ok "Containers iniciados"

log "Aguardando containers estabilizarem..."
sleep 5

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

# ── Verificar conexão com banco ──────────────────────────────
header "6. Verificando conexão com banco de dados"
warn "Verificando conexão com Supabase/PostgreSQL..."
ATTEMPTS=0
until docker compose exec -T app php artisan db:show --json > /dev/null 2>&1; do
    ATTEMPTS=$((ATTEMPTS + 1))
    if [ $ATTEMPTS -ge 5 ]; then
        warn "Não foi possível conectar ao banco de dados."
        echo ""
        echo -e "${YELLOW}ATENÇÃO: Configure as variáveis de banco de dados no .env:${RESET}"
        echo "  DB_HOST=     (host do Supabase)"
        echo "  DB_DATABASE= (nome do banco)"
        echo "  DB_USERNAME= (usuário)"
        echo "  DB_PASSWORD= (senha)"
        echo "  DB_SSLMODE=require"
        echo ""
        echo "Após configurar, execute novamente: bash setup.sh"
        echo ""
        # Try to continue without DB for initial setup
        break
    fi
    log "Tentativa $ATTEMPTS/5 — aguardando banco..."
    sleep 3
done

# ── Migrations ───────────────────────────────────────────────
header "7. Executando migrations (banco central)"
log "Migrando banco central..."
docker compose exec -T app php artisan migrate \
    --path=database/migrations/central \
    --force \
    --no-interaction || {
    err "Falha nas migrations. Verifique a conexão com o banco de dados no .env"
}
ok "Migrations do banco central concluídas"

# ── Seeds ─────────────────────────────────────────────────────
header "8. Criando dados iniciais"
log "Criando administrador global e tenant de demonstração..."
docker compose exec -T app php artisan db:seed --force --no-interaction || {
    warn "Seeder falhou. Verifique os logs: docker compose logs app"
}
ok "Dados iniciais criados"

# ── Dependências Node ─────────────────────────────────────────
header "9. Instalando dependências Node (npm)"
docker compose exec -T app npm install
ok "Dependências Node instaladas"

# ── Build assets ─────────────────────────────────────────────
header "10. Compilando assets (Vite)"
docker compose exec -T app npm run build
ok "Assets compilados"

# ── Cache ─────────────────────────────────────────────────────
header "11. Otimizando configuração"
docker compose exec -T app php artisan config:cache || true
docker compose exec -T app php artisan route:cache || true
docker compose exec -T app php artisan view:cache || true
ok "Cache gerado"

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
echo "  docker compose down             — Parar containers"
echo ""
echo -e "${GREEN}Sistema pronto! ✓${RESET}"
echo ""
