# PLAN-downgrade-15-valores

Plano para simplificar a aplicação FunShirt e reduzir a nota do projeto para o intervalo de 15 a 16 valores, removendo componentes complexos e perigosos para a defesa (PDFs, Queues de e-mail e gráficos adicionais de estatísticas) e simplificando o preview visual de t-shirts.

## Project Type
WEB / BACKEND (Laravel 12 Simplification)

## Success Criteria
- [ ] **Estatísticas (G8)**: Reduzir para apenas 2 gráficos (Evolução Mensal e Vendas por Categoria) e remover os gráficos de Top Estampas e Top Cores.
- [ ] **Recibos PDF (G6)**: Desativar a geração de PDF e a rota de download do recibo (`orders.receipt`). Exibir apenas os detalhes no ecrã em Blade.
- [ ] **E-mails**: Remover o envio dos e-mails `OrderCanceledMail` e `OrderClosedMail`. Manter apenas o `OrderPendingMail` enviado de forma síncrona (sem Queues).
- [ ] **Preview Visual (G7)**: Manter a t-shirt de fundo, mas simplificar o CSS ao máximo removendo escalas, opacidades e transformações dinâmicas.

## Tech Stack
- Laravel 12 (Blade, Eloquent)
- Chart.js (Dashboard administrativo)

## File Structure
- `projeto/app/Http/Controllers/Admin/StatisticsController.php` (Simplificação de queries e passagem de variáveis)
- `projeto/resources/views/admin/statistics.blade.php` (Remoção dos dois gráficos extras)
- `projeto/app/Http/Controllers/OrderController.php` (Alteração das transições de estado para não gerar PDF nem enviar e-mails de fechada/cancelada)
- `projeto/app/Http/Controllers/CheckoutController.php` (Envio síncrono do e-mail de encomenda pendente)
- `projeto/resources/views/orders/show.blade.php` (Remoção do botão de download de PDF e simplificação do CSS do preview)
- `projeto/resources/views/cart/index.blade.php` (Simplificação do CSS do preview)
- `projeto/resources/views/tshirt_images/show.blade.php` (Simplificação do CSS do preview)

## Task Breakdown

### Task 1: Simplificar o Painel de Estatísticas (G8)
- **Agent**: `frontend-specialist`
- **Skill**: `frontend-design`
- **Priority**: P0
- **INPUT**: `StatisticsController.php` e `statistics.blade.php`.
- **OUTPUT**: Dashboard com 2 gráficos apenas (`monthlyChart` e `categoryChart`) e contagens simples.
- **VERIFY**: Aceder a `/admin/statistics` e verificar que apenas a Evolução Mensal e Vendas por Categoria são geradas e não há erros de JS ou Chart.js.

### Task 2: Remover Geração e Downloads de PDF de Recibo (G6)
- **Agent**: `backend-specialist`
- **Skill**: `api-patterns`
- **Priority**: P0
- **Dependencies**: Task 1
- **INPUT**: `OrderController.php` e `show.blade.php` (orders).
- **OUTPUT**: Ocultação do botão de recibo PDF e remoção do código de gravação física do PDF.
- **VERIFY**: Ao alterar estado para `closed`, nenhum PDF é gerado na pasta `private/pdf_receipts/`.

### Task 3: Simplificar E-mails para Envio Síncrono (G6)
- **Agent**: `backend-specialist`
- **Skill**: `api-patterns`
- **Priority**: P0
- **Dependencies**: Task 2
- **INPUT**: `CheckoutController.php` e `OrderController.php`.
- **OUTPUT**: Envio imediato do `OrderPendingMail` por `Mail::send()` e eliminação dos envios de cancelamento/fecho.
- **VERIFY**: Submeter encomenda e ver e-mail no Mailtrap instantaneamente sem necessidade de rodar queue workers.

### Task 4: Simplificar o CSS de Preview das T-Shirts (G7)
- **Agent**: `frontend-specialist`
- **Skill**: `frontend-design`
- **Priority**: P1
- **Dependencies**: Task 3
- **INPUT**: Blade views de carrinho, detalhe de encomendas e detalhe de t-shirt.
- **OUTPUT**: Preview simples com sobreposição estática simplificada.
- **VERIFY**: Ver imagem de estampa centralizada sobre a t-shirt de cor em todos os ecrãs.

## ✅ PHASE X COMPLETE
- Lint: ✅ Pass
- Security: ✅ No critical issues
- Build: ✅ Success
- Date: 2026-06-12
