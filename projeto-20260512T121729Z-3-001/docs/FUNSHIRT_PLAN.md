# 👕 Loja FunShirt - Especificação e Plano de Desenvolvimento Exaustivo

Este documento define as diretrizes, regras de negócio e o roadmap técnico para a implementação da plataforma **FunShirt** em Laravel, consolidando os requisitos do NotebookLM com os padrões de excelência dos projetos `ai-laravel` anteriores.

---

## 🚫 1. Regras e Restrições Críticas (Informativo e Obrigatório)

O não cumprimento destas regras invalida os requisitos do projeto:

*   **Arquitetura**: Obrigatoriamente **Server-Side** (Laravel 13 + SQLite).
*   **Proibição de JS**: Não é permitido o uso de frameworks SPA (Vue, React) ou lógica complexa em JavaScript puro.
*   **Livewire**: Única exceção permitida para interatividade rica, mantendo a lógica de controlo no PHP/Servidor.
*   **Pacotes Proibidos**: Proibido o uso de Laravel Nova, Filament, ou pacotes que resolvam nativamente o carrinho/pagamentos.
*   **Princípio DRY**: Uso obrigatório de **Blade Partials** e **Componentes** para evitar duplicação de código visual.

---

## 🏗️ 2. Modelo de Domínio e Base de Dados

A estrutura deve seguir o diagrama ER oficial, com as seguintes regras técnicas:

### Regras de Tabelas
1.  **Users & Customers (Herança 1:1)**:
    *   Um `User` pode ser Admin (A), Funcionário (F) ou Cliente (C).
    *   O `id` do Customer deve ser igual ao `id` do User correspondente (não autoincrementável).
    *   **Soft Deletes**: A remoção de clientes só utiliza *soft delete* se o cliente já possuir encomendas ou imagens próprias. Caso contrário, pode ser remoção física.
2.  **TshirtImages (Catálogo vs Privado)**:
    *   `customer_id == null` -> Catálogo público (pode ter `category_id` ou ser **null** para imagens sem categoria específica).
    *   `customer_id != null` -> Imagem privada (obrigatório ter `category_id` a **null**).
3.  **Colors & Assets**:
    *   O ficheiro da t-shirt base em `storage/app/public/tshirt_base` deve ter o nome **exatamente igual ao código CSS da cor** (ex: `white.jpg` para a cor `#ffffff` ou `white`).
4.  **Orders & Imutabilidade**:
    *   `unit_price` e dados da imagem devem ser persistidos no `OrderItem` para que alterações de preço no catálogo não alterem encomendas passadas.

---

## 🛠️ 3. Lógica Aplicacional e Regras de Negócio

### G1. Autenticação e Perfis
*   **Verificação**: Registo de novos clientes exige **confirmação de e-mail**.
*   **Recuperação**: Suporte a *reset* de password via link enviado por e-mail.
*   **Acesso Funcionário**: Os funcionários **não podem editar o próprio perfil**; a gestão dos seus dados é exclusiva dos Administradores.
*   **Comunicação**: Uso obrigatório do **Mailtrap.io** para todos os envios.

### G2. Carrinho de Compras (Session-Based)
*   **Separação por Tamanho**: T-shirts com a mesma imagem/cor mas tamanhos diferentes (ex: M e L) devem ser **itens separados** (linhas diferentes).
*   **Limpeza**: Ação explícita para "Limpar Carrinho" (remover todos os itens de uma vez).
*   **Descontos**: O `qty_discount` aplica-se a **cada linha individualmente** (ex: se o limiar for 10, apenas o item que atingir 10 unidades tem desconto).

### G3. Checkout e Pagamentos (API Externa)
*   **Simulação de Falhas**: A aplicação **não valida** internamente se o Visa começa por "40" ou MB WAY por "90". Deve enviar o pedido e estar preparada para tratar o erro **HTTP 422** retornado pela API.
*   **Precisão**: O valor (`value`) enviado deve ter no máximo **2 casas decimais**.
*   **Endpoint**: `https://ainet-payments-api.vercel.app/api/payments`.

### G4. Gestão e Logística
*   **Filtros Admin**: Listagem de encomendas deve ser filtrável por **estado, cliente e data**.
*   **Anulação**: Ao anular, o administrador pode preencher `reason_for_cancellation`. Esta razão deve ser visível para o Cliente e Admin no detalhe da encomenda.
*   **Recibos**: Gerados em PDF (DomPDF) apenas quando o estado passa a `closed`.

---

## 🚀 4. Roadmap de Implementação

### Fase 1: Fundação
1.  Migrações e Models com `SoftDeletes` e `Cast` de tipos.
2.  `DatabaseSeeder` com senha padrão "123".

### Fase 2: Catálogo e Carrinho
1.  Implementação do `CartService` (gestão da sessão).
2.  Lógica de separação por tamanho e cálculo de subtotais dinâmicos.

### Fase 3: Checkout e Integração API
1.  Formulário de checkout pré-preenchido.
2.  Tratamento de exceções e erros HTTP (422) da API de pagamentos.

### Fase 4: Áreas Administrativas
1.  Dashboards com filtros avançados.
2.  Geração de PDFs e integração com Mailtrap.

---

## 🔒 5. Segurança, Performance e Padrões

*   **Consultas Seletivas**: Utilizar `select('col1', 'col2')` para evitar carregar dados desnecessários e minimizar o payload.
*   **Eager Loading**: Evitar N+1 com `with(['items', 'customer'])`.
*   **Policies**: Proteção rigorosa de rotas: Clientes só acedem aos seus próprios dados/imagens.
*   **Preview Dinâmico (Extra)**: Se implementado, guardar ajustes (escala, posição) no campo `custom` (JSON) da tabela `tshirt_images`.

---

> **Contrato Técnico**: Este plano é exaustivo e deve ser seguido para garantir a aprovação nos requisitos. Qualquer desvio deve ser documentado.
