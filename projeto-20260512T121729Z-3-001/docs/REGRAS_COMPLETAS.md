# 👕 FunShirt - Manual de Regras e Especificações Técnicas (Completo)

Este documento é a fonte única de verdade para o projeto, consolidando todos os detalhes técnicos e regras de negócio extraídos da especificação oficial.

---

## 📊 1. Cotações e Pesos (Avaliação)
- **G1. Autenticação e Perfis (20%)**: Registo, Login Manual, Reset de Password (Mailtrap).
- **G2. Catálogo (20%)**: CRUD de categorias e imagens, upload de ficheiros.
- **G3. Carrinho de Compras (25%)**: Persistência em sessão, gestão de tamanhos (XS-XL) e cores.
- **G4. Encomendas - Cliente e Admin (25%)**: Checkout, Histórico, Gestão de Estados, PDF.
- **G5-G10. Extras e Dashboards (10%)**: Estatísticas, Emails, Preview, Inventário.

---

## 🏗️ 2. Arquitetura e Restrições Obrigatórias
- **Framework**: Laravel 12+ (Server-Side).
- **BD**: SQLite (Migrações e Seeders fornecidos não podem ser alterados).
- **Frontend**: **Proibido JS complexo** (Vue/React). Permitido **Livewire**.
- **Autenticação**: **Implementação Manual obrigatória**. Proibido Jetstream, Breeze, Filament ou Nova.
- **Padrões**: Uso de Blade Components, Partials, Eloquent (Eager Loading), Policies e Form Requests.

---

## 👥 3. Perfis e Permissões Detalhadas
1.  **Anónimo**: Consulta catálogo, gere carrinho, regista-se como cliente.
2.  **Cliente**: Gere perfil/avatar, gere imagens privadas, faz checkout, consulta histórico e descarrega recibos.
3.  **Funcionário**: Consulta catálogo, gere encomendas pendentes (Pending -> Closed). **Não pode editar o próprio perfil nem realizar checkout**.
4.  **Administrador**: Gestão total de utilizadores (incluindo Staff), categorias, cores, preços e estatísticas. Pode cancelar qualquer encomenda. **Não pode realizar checkout**.

---

## 🗄️ 4. Especificação da Base de Dados (Esquema)

### Tabelas Principais:
- **`users`**: `id`, `name`, `email`, `password`, `user_type` (C/F/A), `gender` (M/F), `blocked` (boolean), `photo_url`.
- **`customers`**: `id` (FK para users, não autoincrementável), `nif` (9 dígitos), `address`, `default_payment_type`, `default_payment_ref`.
- **`categories`**: `id`, `name`, `image_url`.
- **`prices`**: `id`, `unit_price_catalog`, `unit_price_own`, `unit_price_catalog_discount`, `unit_price_own_discount`, `qty_discount` (limiar de desconto).
- **`colors`**: `code` (PK/CSS code), `name`. (Imagem base deve ter o nome igual ao `code`).
- **`tshirt_images`**: `id`, `customer_id` (null se catálogo), `category_id`, `name`, `description`, `image_url`, `custom` (JSON para preview).
- **`orders`**: `id`, `status` (pending/closed/canceled), `customer_id`, `date`, `total_price`, `nif`, `address`, `payment_type`, `payment_ref`, `receipt_url`.
- **`order_items`**: `id`, `order_id`, `tshirt_image_id`, `color_code`, `size` (XS-XL), `qty`, `unit_price`, `sub_total`, `custom` (JSON).

---

## 🛒 5. Lógica de Carrinho e Preços
- **Sessão**: O carrinho deve ser mantido em `Session`, persistindo após o login.
- **Itens Únicos**: Mesma imagem + Mesma Cor + Tamanhos Diferentes = Itens Separados no Carrinho.
- **Cálculo de Preço**:
    - Se `qty >= prices.qty_discount`, usa o preço de desconto correspondente.
    - O preço unitário deve ser **congelado** no momento do checkout na tabela `order_items`.

---

## 💳 6. Pagamentos e Simulação de Erros
- **Endpoint**: `https://ainet-payments-api.vercel.app/api/payments`
- **Validação Local (FunShirt)**:
    - **Visa**: 16 dígitos, começa por '4'.
    - **MB WAY**: 9 dígitos, começa por '9'.
    - **PayPal**: Email válido.
- **Simulação de Erros (A passar para a API)**:
    - **Inexistente**: Visa começa por '40', PayPal por 'xx', MB WAY por '90'. (API retorna 422).
    - **Saldo Insuficiente**: (> 20€): Visa começa por '49', PayPal por 'zz', MB WAY por '99'.

---

## 📁 7. Storage e Media
- **`storage/app/public/`**:
    - `categories/`, `photos/`, `tshirt_base/`, `tshirt_images/`.
- **`storage/app/private/`**:
    - `pdf_receipts/`, `tshirt_images_private/`.
- **Regra**: Ao apagar registos, os ficheiros físicos devem ser removidos do storage.

---

## 📧 8. Comunicações (Mailtrap)
- **Registo**: Confirmação de email obrigatória.
- **Nova Encomenda**: Email de confirmação (estado "Pending").
- **Encomenda Fechada**: Email com **Recibo PDF em anexo**.
- **Encomenda Anulada**: Email informativo.

---

## ✅ 9. Regras de Ouro para Implementação
1.  **Princípio DRY**: Usar `@include` ou `<x-component>` para tabelas de encomendas e listas de t-shirts.
2.  **Integridade**: Ao criar Cliente, criar primeiro o `User` e depois o `Customer` com o mesmo ID.
3.  **Segurança**: Nunca permitir que um Cliente aceda a imagens personalizadas de outros (`customer_id`).
4.  **Soft Deletes**: Usar obrigatoriamente em `Customers` com encomendas para manter integridade histórica.
