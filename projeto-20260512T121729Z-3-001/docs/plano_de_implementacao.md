# 🗺️ Plano de Implementação — Projeto FunShirt

Este documento apresenta a análise do estado atual do projeto **FunShirt** e um plano passo a passo detalhado em formato de checklist para guiar a implementação de todas as funcionalidades em falta, garantindo conformidade com os requisitos do enunciado.

---

## 🔍 Estado Atual do Projeto

Efetuámos uma análise à estrutura de ficheiros e código existente na pasta [projeto](file:///e:/Aplicacoes_internet/projeto-20260512T121729Z-3-001/projeto):

1. **Base de Dados e Migrações (100% Concluído):**
   - As migrações da base de dados fornecidas pelo docente estão criadas e prontas a usar (tabelas `users`, `customers`, `prices`, `categories`, `colors`, `tshirt_images`, `orders` e `order_items`).
   - Os seeders (`DatabaseSeeder`, `UsersSeeder`, `TshirtImagesSeeder`, `OrdersSeeder`, etc.) estão implementados para carregar os dados de teste e imagens.

2. **Modelos Eloquent (95% Concluído):**
   - Modelos criados para todas as tabelas: `User`, `Customer`, `Category`, `Color`, `Tshirt_image`, `Order`, `Order_item` e `Price` com os seus relacionamentos básicos.
   - **Nota de Correção:** No modelo `Order.php`, a coluna `reason_for_cancellation` (conforme definido na migração com dois 'l') está incorretamente listada como `reason_for_cancelation` (com um 'l') no array `$fillable`. Isto será corrigido no plano.

3. **Autenticação (30% Concluído):**
   - Login, Logout e Registo básico de Clientes implementados manualmente em `LoginController` e `RegisterController`.
   - Rotas de login/register/logout configuradas em `routes/web.php`.
   - Vistas básicas criadas em `resources/views/auth/login.blade.php` e `auth/register.blade.php`.
   - *Falta:* Recuperação de senha, Verificação de e-mail obrigatória, bloqueio efetivo de acessos, CRUDs de utilizadores e separação de perfis.

4. **Catálogo de Imagens (50% Concluído):**
   - Página principal (`/`) mapeada para `TshirtimageController@shop` que lista as imagens paginadas com filtros de Categoria, Condição (Catálogo vs Próprias) e Nome.
   - Vista `tshirt_images/shop.blade.php` exibe a lista.
   - Vista de detalhe `/tshirt-images/{tshirtImage}` implementada em `TshirtimageController@show` e `tshirt_images/show.blade.php`.
   - *Falta:* CRUDs de gestão de categorias, imagens, cores e preços para Administradores.

5. **Carrinho de Compras (60% Concluído):**
   - Gerido na sessão do servidor.
   - `CartController` implementa adicionar, atualizar quantidade, remover e limpar carrinho.
   - Vista `cart/index.blade.php` exibe a lista de itens e o total.
   - *Falta:* Integração com a lógica de descontos por quantidade da tabela `prices`, ligação ao checkout e exibição visual do preview da t-shirt.

6. **Estrutura Visual (60% Concluído):**
   - Layout principal em `layouts/app.blade.php` com Navbar, Cabeçalho dinâmico e Rodapé.
   - Ficheiro de estilos customizados em `public/css/premium.css`.
   - Integração com Livewire Flux e Tailwind CSS v4 na estrutura.

---

## 📋 Checklist de Implementação por Grupo

### 🟢 Grupo 1: Autenticação, Perfil e Gestão de Utilizadores (Peso: 20%) [CONCLUÍDO]

- [x] **Email Verification (Verificação de E-mail):**
  - [x] Implementar a verificação de e-mail obrigatória para novos clientes registados.
  - [x] Proteger as rotas de clientes com o middleware `verified`.
  - [x] Configurar o Mailtrap.io em `.env` para envio de e-mails.
- [x] **Recuperação de Senha (Password Reset):**
  - [x] Configurar rotas e vistas para "Esqueci-me da senha".
  - [x] Enviar link de redefinição por e-mail e processar a redefinição de senha com segurança.
- [x] **Perfil do Cliente:**
  - [x] Criar a rota e vista `profile.edit`.
  - [x] Permitir a edição de dados pessoais: Nome, E-mail, Género.
  - [x] Permitir a edição de dados de faturação/envio do Customer: NIF (9 dígitos), Morada, Método de pagamento preferencial e Referência de pagamento.
  - [x] Implementar o upload de fotografia/avatar (guardado em `storage/app/public/photos/`).
  - [x] Permitir a alteração de senha de acesso.
- [x] **Gestão de Utilizadores (Exclusivo Administrador):**
  - [x] Criar `Admin\UserController`.
  - [x] Listar e filtrar todos os utilizadores (Administradores, Funcionários e Clientes).
  - [x] CRUD de Funcionários e Administradores (Criar, Editar, Bloquear e Excluir).
  - [x] Listar, filtrar, bloquear/desbloquear e remover Clientes.
  - [x] Implementar Soft Delete para clientes com encomendas/imagens personalizadas associadas (para não quebrar histórico).
  - [x] Restringir acesso: Garantir que Administradores **não** conseguem aceder/editar perfis privados dos clientes.
- [x] **Políticas de Acesso (Policies / Middleware):**
  - [x] Implementar `UserPolicy` ou Middlewares baseados em `user_type` (`C` = Cliente, `F` = Funcionário, `A` = Administrador).
  - [x] Bloquear utilizadores com o estado `blocked = 1` de iniciar sessão (ou fazer logout imediato se forem bloqueados enquanto logados).

---

### 🟢 Grupo 2: Catálogo (Peso: 20%) [CONCLUÍDO ✅ — 19/19 testes validados]

- [x] **Melhorias na Consulta (Público):**
  - [x] Ajustar layout e usabilidade da loja (`shop.blade.php`).
  - [x] Filtros por nome/descrição e categoria com `appends(request()->query())` para manter o estado na paginação.
  - [x] Catálogo público lista **apenas** imagens com `customer_id IS NULL` (separação de imagens de catálogo vs. privadas).
  - [x] Ordenação descendente por `id` para garantir que registos recém-criados aparecem em primeiro lugar.
- [x] **CRUD de Categorias (Administrador):**
  - [x] Criado `Admin\CategoryController` com CRUD completo.
  - [x] Formulário de criação/edição com upload opcional de imagem para `storage/app/public/categories/`.
  - [x] Listagem com suporte a **Soft Delete** (`deleted_at`). Sem opção de restauro (não pedido no enunciado).
  - [x] Rota: `admin/categories` (resource, excluindo `show`).
- [x] **CRUD de Imagens de T-Shirt / Estampas (Administrador):**
  - [x] Criado `Admin\TshirtImageController` com CRUD completo.
  - [x] Upload obrigatório do ficheiro de imagem de design para `storage/app/public/tshirt_images/`.
  - [x] Associação opcional a uma categoria via `category_id`.
  - [x] Rota: `admin/tshirt-images` (resource, excluindo `show`).
- [x] **CRUD de Cores (Administrador):**
  - [x] Criado `Admin\ColorController` com CRUD completo.
  - [x] Inserção de novas cores com `code` (PK string) e `name`.
  - [x] Upload obrigatório da imagem base da t-shirt para `storage/app/public/tshirt_base/<code>.{png|jpg}`.
  - [x] Accessor `tshirt_base_url` no modelo `Color` resolve dinamicamente a extensão (fallback `.png` → `.jpg`).
  - [x] Rota: `admin/colors` (resource, excluindo `show`).
- [x] **Configuração de Preços (Administrador):**
  - [x] `PriceController` reescrito para gerir o **registo único** via `Price::first()`.
  - [x] Campos editáveis: `unit_price_catalog`, `unit_price_own`, `unit_price_catalog_discount`, `unit_price_own_discount`, `qty_discount`.
  - [x] Rota simplificada: `GET admin/prices/edit` e `PUT admin/prices`.

---

### 🟢 Grupo 3: Carrinho de Compras (Peso: 20%) [CONCLUÍDO ✅ — 11/11 testes validados]

- [x] **Lógica de Descontos no Carrinho:**
  - [x] Obter as configurações da tabela `prices` no controlador.
  - [x] No `CartController@buildCartItems`, verificar se a quantidade total de um mesmo item (imagem + cor + tamanho) é superior ou igual a `qty_discount`.
  - [x] Aplicar o preço de desconto correspondente (`unit_price_catalog_discount` ou `unit_price_own_discount`).
  - [x] Exibir de forma clara no carrinho quando um desconto está ativo (ex: riscar preço antigo e exibir badge de desconto).
- [x] **Interface e Usabilidade:**
  - [x] Adicionar miniaturas com o preview da t-shirt (estampa sobreposta na cor base do modelo Color).
  - [x] Exibir subtotal detalhado por linha e o total global acumulado.
  - [x] Integrar botão de "Checkout" com redirecionamento inteligente (redireciona para login/registo se for anónimo, preservando o carrinho).
  - [x] Permitir alteração de cor/tamanho diretamente no carrinho com fusão de quantidades (colisão).
  - [x] Remoção automática de item ao reduzir a quantidade para 0.
  - [x] Botão para esvaziar/limpar o carrinho completo com confirmação JS.

---

### 🟢 Grupo 4: Encomendas e Checkout (Peso: 20%) [CONCLUÍDO ✅ — 8/8 testes validados]

- [x] **Fluxo de Checkout (Exclusivo Cliente):**
  - [x] Criar a rota e vista `checkout.index` (apenas para clientes verificados).
  - [x] Formulário de confirmação com dados pré-preenchidos: NIF, Morada de entrega, Método de pagamento (Visa, PayPal, MB WAY) e Referência.
  - [x] Permitir a edição destes dados para a encomenda em curso.
- [x] **Simulação de Pagamento (Integração de API):**
  - [x] Implementar chamada HTTP via `Http::post()` para `https://ainet-payments-api.vercel.app/api/payments`.
  - [x] Validar formato de referências no cliente antes de enviar:
    - *Visa:* 16 dígitos iniciados por `4`.
    - *PayPal:* E-mail válido.
    - *MB WAY:* 9 dígitos iniciados por `9`.
  - [x] Lidar com respostas de erro (ex: `422 Unprocessable Entity`) e exibir mensagens amigáveis ao utilizador (sem abortar a aplicação).
- [x] **Registo da Encomenda:**
  - [x] Em caso de sucesso de pagamento, criar o registo na tabela `orders` com estado `pending`.
  - [x] Inserir os itens na tabela `order_items` replicando os preços e descontos vigentes no momento (imutabilidade histórica).
  - [x] Esvaziar o carrinho de compras.
- [x] **Consulta de Encomendas:**
  - [x] **Cliente:** Ver o seu histórico de encomendas com detalhes e descarregar recibo PDF (apenas para estado `closed`).
  - [x] **Funcionário:** Ver lista de encomendas com estado `pending` e os seus detalhes completos.
  - [x] **Administrador:** Ver e filtrar todas as encomendas por estado, cliente e data, com acesso a todos os detalhes e PDFs.
- [x] **Transições de Estado:**
  - [x] **Funcionário:** Pode alterar estado de `pending` para `closed` (apenas encomendas pendentes).
  - [x] **Administrador:** Pode alterar para `closed` ou `canceled` (com campo opcional de justificação da anulação `reason_for_cancellation`).

---

### 🟢 Grupo 5: Imagens Personalizadas (Peso: 5%) [CONCLUÍDO ✅ — 14/14 testes validados]

- [x] **CRUD de Imagens Próprias (Cliente):**
  - [x] Criar a rota e vista `my-images.index`.
  - [x] Upload obrigatório de imagens privadas para `storage/app/private/tshirt_images_private/` (protegido contra acessos diretos por URL).
  - [x] Associar o `customer_id` do cliente logado. O `category_id` deve ser guardado como `null`.
- [x] **Segurança e Privacidade:**
  - [x] Criar rotas controladas para servir as imagens privadas através de um Controller (ex: `PrivateImageController@serve`).
  - [x] Aplicar Policies: Apenas o cliente proprietário, funcionários (no contexto de processar uma encomenda) e administradores podem ver as imagens personalizadas. Impedir acesso a outros clientes ou anónimos.
- [x] **Compra com Imagem Própria:**
  - [x] Permitir adicionar imagens privadas do cliente ao carrinho a partir da sua galeria privada, aplicando o preço unitário próprio configurado (`unit_price_own` / `unit_price_own_discount`).

---

### 🟡 Grupo 6: Recibos e E-mail (Peso: 5%)

- [ ] **Geração de Recibos em PDF:**
  - [ ] Instalar um pacote gerador de PDF (recomendado: `barryvdh/laravel-dompdf`).
  - [ ] Implementar a geração automática do PDF aquando da transição para o estado `closed`.
  - [ ] Guardar o PDF em `storage/app/private/pdf_receipts/`.
  - [ ] O PDF deve conter: Logótipo da FunShirt, dados do cliente (NIF, nome), data da encomenda, lista detalhada de itens com valores e total final.
  - [ ] Proteger o download do PDF: apenas o cliente proprietário e administradores podem descarregar.
- [ ] **Notificações por E-mail (Mailtrap.io):**
  - [ ] Criar classes de email (`Mailable`):
    - [ ] `OrderPendingMail`: Enviado ao cliente quando a encomenda é criada.
    - [ ] `OrderCanceledMail`: Enviado ao cliente quando a encomenda é cancelada.
    - [ ] `OrderClosedMail`: Enviado ao cliente quando a encomenda é enviada, com o recibo PDF anexado.
  - [ ] Configurar filas de processamento (`Queues` com base de dados ou sync) para enviar os e-mails sem atrasar a resposta web.

---

### 🟢 Grupo 7: Preview de T-Shirts (Peso: 5%) [CONCLUÍDO ✅]

- [x] **Lógica de Preview Visual:**
  - [x] Desenvolver um componente Blade (ou vista parcial) reutilizável para o preview.
  - [x] Usar sobreposição via CSS:
    - Div contentora com posição relativa.
    - Imagem base da t-shirt (cor selecionada) por baixo.
    - Imagem do design (estampa) por cima, com posição absoluta, centralizada e dimensões ajustadas (ex: `width: 40%; top: 25%; left: 30%;`).
- [x] **Integração:**
  - [x] Adicionar o preview ao Carrinho de compras.
  - [x] Adicionar o preview aos Detalhes da Encomenda.
  - [x] (Opcional) Incluir miniatura de preview no recibo PDF gerado.

---

### 🟢 Grupo 8: Estatísticas (Peso: 5%) [CONCLUÍDO ✅ — 6/6 testes validados]

- [x] **Painel de Estatísticas (Administrador):**
  - [x] Criar a rota e vista `admin.statistics`.
  - [x] Implementar consultas agregadas no base de dados usando Eloquent:
    - [x] Volume total de vendas (faturação acumulada).
    - [x] Número de encomendas por estado.
    - [x] Imagens de catálogo e cores mais vendidas.
    - [x] Clientes que mais compraram.
    - [x] Evolução de vendas mensal/anual.
  - [x] Exibir os dados em tabelas limpas e com gráficos interativos (sugestão: usar a biblioteca Chart.js via CDN).

---

## 🛠️ Ordem de Desenvolvimento Recomendada (Roteiro)

Recomendamos a implementação na seguinte ordem cronológica devido às dependências entre os módulos:

```
┌──────────────────────────────────────────┐
│ FASE 1: Autenticação, Perfis e E-mail    │ (G1 & G6 parte e-mail)
└────────────────────┬─────────────────────┘
                     ▼
┌──────────────────────────────────────────┐
│ FASE 2: Melhorias do Catálogo e CRUDs    │ (G2 & G5)
└────────────────────┬─────────────────────┘
                     ▼
┌──────────────────────────────────────────┐
│ FASE 3: Lógica do Carrinho e Descontos   │ (G3 & G7 parte preview)
└────────────────────┬─────────────────────┘
                     ▼
┌──────────────────────────────────────────┐
│ FASE 4: Checkout, Pagamento e Encomendas │ (G4)
└────────────────────┬─────────────────────┘
                     ▼
┌──────────────────────────────────────────┐
│ FASE 5: Recibos PDF e Transições         │ (G6 recibos)
└────────────────────┬─────────────────────┘
                     ▼
┌──────────────────────────────────────────┐
│ FASE 6: Estatísticas e Polimento         │ (G8 & UX/Acessibilidade)
└──────────────────────────────────────────┘
```

---

## 🧪 Plano de Verificação e Testes

Para garantir que o código cumpre os requisitos transversais (MVC, Eloquent, DRY, Segurança e Performance):

1. **Testes de Integração com API de Pagamento:**
   - Testar referências corretas e incorretas para validar o comportamento da API simulada.
   - Garantir que erros de saldo insuficiente ou conta inexistente são capturados e tratados.

2. **Verificação de E-mails:**
   - Validar na consola do **Mailtrap.io** se todos os e-mails de mudança de estado (`pending`, `closed`, `canceled`) são entregues ao cliente correto.

3. **Auditoria de Segurança (Policies):**
   - Tentar aceder diretamente às rotas de download de PDF de outro utilizador e verificar se retorna erro HTTP 403.
   - Tentar aceder às rotas administrativas com uma conta de Cliente e garantir o redirecionamento ou erro HTTP 403.
   - Validar que utilizadores bloqueados não conseguem fazer login.

4. **Ferramenta de Auditoria Automática:**
   - Correr as ferramentas de validação do projeto:
     - `php artisan test` (para validar testes unitários e de integração existentes).
     - `npm run dev` para desenvolvimento local.
