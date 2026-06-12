# PLAN-db-comparison

Plano para comparar as bases de dados local (DB A) e externa (DB B) a fim de identificar divergências em termos de tamanho, esquemas, tabelas, registros e índices.

## Project Type
BACKEND (Database Analysis)

## Success Criteria
- [x] Localizar as duas bases de dados SQLite.
- [x] Extrair lista de tabelas e comparar a existência delas em ambas as bases.
- [x] Verificar diferenças estruturais (colunas e tipos) na tabela `users`.
- [x] Comparar volumetria de dados (contagem de registros por tabela).
- [x] Comparar quantidade e definição de índices ativos.
- [x] Gerar relatório detalhado sem aplicar alterações nas bases.

## Tech Stack
- SQLite (Banco de dados)
- Python (Script de automação para comparação)

## File Structure
- `projeto-20260512T121729Z-3-001/projeto/database/database.sqlite` (Local)
- `d:/database_folder/database/database.sqlite` (Externa)

## Task Breakdown

### Task 1: Mapear Caminhos e Verificar Tamanhos
- **Agent**: `database-architect`
- **Skill**: `database-design`
- **Priority**: P0
- **Dependencies**: Nenhuma
- **INPUT**: Caminhos das bases de dados.
- **OUTPUT**: Validação de existência e tamanhos em bytes.
- **VERIFY**: Executar comando de listagem e obter os tamanhos corretos dos ficheiros.

### Task 2: Extração de Esquemas e Tabelas
- **Agent**: `database-architect`
- **Skill**: `database-design`
- **Priority**: P0
- **Dependencies**: Task 1
- **INPUT**: Conexões SQLite ativas.
- **OUTPUT**: Lista de tabelas e schemas estruturais de cada tabela.
- **VERIFY**: Verificar tabelas adicionais do Laravel Telescope na base local.

### Task 3: Análise de Dados e Índices
- **Agent**: `database-architect`
- **Skill**: `database-design`
- **Priority**: P1
- **Dependencies**: Task 2
- **INPUT**: Tabelas comuns encontradas.
- **OUTPUT**: Contagem de registros por tabela e lista de índices.
- **VERIFY**: Listagem de diferenças numéricas exatas de registros.

## ✅ PHASE X COMPLETE
- Lint: ✅ Pass
- Security: ✅ No critical issues
- Build: ✅ Success
- Date: 2026-06-12
