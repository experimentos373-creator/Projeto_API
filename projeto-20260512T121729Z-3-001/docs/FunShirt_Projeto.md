# 🎽 Projeto FunShirt — Aplicações para a Internet
**Engenharia Informática | 2º Ano · 2º Semestre | 2025-26 | Avaliação Periódica**

| Campo | Detalhe |
|---|---|
| Data de Publicação | 11 de Abril de 2026 |
| Data de Entrega | 13 de Junho de 2026 |
| Prazo de Resultados | 29 de Junho de 2026 |

---

## Índice

1. [Objetivo](#1-objetivo)
2. [Cenário de Negócio](#2-cenário-de-negócio)
3. [Perfis de Acesso e Funcionalidades](#3-perfis-de-acesso-e-funcionalidades)
4. [Projeto e Restrições Técnicas](#4-projeto-e-restrições-técnicas)
5. [Grupos de Funcionalidades](#5-grupos-de-funcionalidades)
   - [G1 — Autenticação, Perfil e Gestão de Utilizadores](#g1--autenticação-perfil-e-gestão-de-utilizadores)
   - [G2 — Catálogo](#g2--catálogo)
   - [G3 — Carrinho de Compras](#g3--carrinho-de-compras)
   - [G4 — Encomendas](#g4--encomendas)
   - [G5 — Imagens Personalizadas](#g5--imagens-personalizadas)
   - [G6 — Recibos e E-mail](#g6--recibos-e-e-mail)
   - [G7 — Preview de T-Shirts](#g7--preview-de-t-shirts)
   - [G8 — Estatísticas](#g8--estatísticas)
6. [Base de Dados](#6-base-de-dados)
   - [Diagrama ER (Resumo)](#diagrama-er-resumo)
   - [Tabela: users](#tabela-users)
   - [Tabela: customers](#tabela-customers)
   - [Tabela: categories](#tabela-categories)
   - [Tabela: prices](#tabela-prices)
   - [Tabela: colors](#tabela-colors)
   - [Tabela: tshirt_images](#tabela-tshirt_images)
   - [Tabela: orders](#tabela-orders)
   - [Tabela: order_items](#tabela-order_items)
   - [Convenções e Campos Comuns](#convenções-e-campos-comuns)
7. [Storage — Estrutura de Ficheiros](#7-storage--estrutura-de-ficheiros)
8. [Plataforma de Pagamentos](#8-plataforma-de-pagamentos)
   - [Endpoint e Payload](#endpoint-e-payload)
   - [Validação](#validação)
   - [Simulação de Pagamentos Inválidos](#simulação-de-pagamentos-inválidos)
9. [Entrega](#9-entrega)
10. [Avaliação](#10-avaliação)
    - [Pesos por Grupo](#pesos-por-grupo)
    - [Requisitos Transversais](#requisitos-transversais)

---

## 1. Objetivo

Implementar uma aplicação Web **server-side** baseada na **Framework Laravel** para a loja online **FunShirt**, que comercializa t-shirts estampadas com imagens de catálogo ou com designs personalizados pelos próprios clientes.

---

## 2. Cenário de Negócio

A **FunShirt** é uma empresa especializada em t-shirts personalizadas. O modelo de negócio assenta nos seguintes pilares:

- Os clientes escolhem **designs de um catálogo** ou **enviam as suas próprias imagens** para o servidor da loja.
- A loja estampa as imagens e envia as t-shirts para a morada dos clientes.
- Existem **quatro categorias de preços configuráveis**: imagens de catálogo ou personalizadas, com e sem descontos de quantidade.

### Ciclo de Vida de uma Encomenda

```
Carrinho (sessão)
      │
      ▼
  Checkout ──► Pagamento Simulado
      │
      ▼ (sucesso)
  [pending] ──► Estampagem + Envio ──► [closed]
      │
      └──────────────────────────────► [canceled]
```

| Estado | Descrição |
|---|---|
| `pending` | Encomenda registada após pagamento bem-sucedido |
| `closed` | Encomenda processada (estampagem + envio concluído) |
| `canceled` | Encomenda anulada (por problema ou decisão administrativa) |

### Carrinho de Compras

- Gerido obrigatoriamente via **sessão do servidor web**.
- Mantém-se ativo mesmo durante autenticação.
- Cada item inclui: imagem, cor, tamanho e quantidade.
- Apresenta preço unitário, subtotal por item e total global.
- Suporta: adição, remoção, alteração de características e limpeza total.
- Reduzir a quantidade de um item para **zero** remove-o automaticamente.

---

## 3. Perfis de Acesso e Funcionalidades

A plataforma estrutura-se em **quatro níveis de acesso**:

### 👤 Utilizador Anónimo
- Consultar informações sobre a loja
- Filtrar e consultar o catálogo de imagens
- Gerir o carrinho de compras
- Registar uma conta de cliente

### 🛒 Cliente
Herda as capacidades do utilizador anónimo, e adicionalmente:
- Gerir o seu perfil (dados pessoais, fotografia/avatar) e senha
- Confirmar encomendas (checkout)
- Consultar o histórico de compras
- Descarregar recibos em PDF (encomendas fechadas)
- Gerir as suas imagens personalizadas (privadas e de uso exclusivo)
- Receber e-mails automáticos nos estados `pending`, `canceled` e `closed` (este último com recibo em anexo)

### 🔧 Funcionário
- Consultar o catálogo de imagens
- Alterar a sua senha
- Consultar a lista de encomendas `pending`
- Transitar encomendas para o estado `closed`

> ⚠️ Os funcionários **não** têm acesso ao seu próprio perfil — a gestão da sua informação compete exclusivamente aos administradores.

### 🛡️ Administrador
- Gerir o seu próprio perfil
- Criar, alterar, bloquear ou remover contas de funcionários e administradores
- Listar, filtrar, bloquear ou remover (soft delete) contas de clientes
- Gestão integral do catálogo (categorias, imagens, cores, preços)
- Filtrar e consultar qualquer encomenda (por estado, cliente e data)
- Transitar encomendas para `closed` ou `canceled`
- Aceder ao painel de estatísticas

> ⚠️ Os administradores **não** têm acesso aos perfis privados dos clientes.

---

## 4. Projeto e Restrições Técnicas

| Aspeto | Regra |
|---|---|
| Framework | **Laravel 12** ou superior |
| Base de Dados | **SQLite** |
| Arquitetura | Obrigatoriamente **server-side** |
| JavaScript | Uso **residual** apenas para pequenos ajustes de interface (efeitos visuais, bibliotecas UI) |
| Exceção JS | **Livewire** é permitido (JS gerado internamente, lógica no servidor) |
| Pacotes extras | Permitidos (ex: Telescope, geradores PDF, gráficos) |
| Pacotes proibidos | Ferramentas de geração automática (Nova, Filament) ou pacotes que resolvam nativamente funcionalidades nucleares (carrinho, pagamento) |

> 💬 Em caso de dúvida sobre um pacote: **marco.monteiro@ipleiria.pt**

---

## 5. Grupos de Funcionalidades

---

### G1 — Autenticação, Perfil e Gestão de Utilizadores

#### Autenticação (todos os perfis)
- Login com **e-mail + senha**
- Alteração de senha após login
- **Logout**
- **Reset de senha** por e-mail com link dedicado

#### Registo de Clientes (anónimos)
- Registo como cliente
- Confirmação/verificação do e-mail obrigatória
- Toda a comunicação por e-mail via **mailtrap.io**

#### Perfil do Cliente
- Consulta e edição de dados pessoais
- Upload de fotografia ou avatar
- Alteração de senha

#### Gestão por Administradores

| Ação | Clientes | Funcionários / Admins |
|---|---|---|
| Listar / filtrar | ✅ | ✅ |
| Criar | ❌ | ✅ |
| Alterar | ❌ | ✅ |
| Bloquear | ✅ | ✅ |
| Remover | ✅ (soft delete se tiver encomendas/imagens) | ✅ |

> ⚠️ A remoção de clientes com encomendas ou imagens próprias deve ser feita via **soft delete** (campo `deleted_at`), preservando o histórico.
> ⚠️ Os administradores **não** têm permissão para aceder aos perfis privados dos clientes.

---

### G2 — Catálogo

#### Consulta (todos os utilizadores, incluindo anónimos)
- Listagem de imagens de t-shirts com nome, descrição e imagem
- Imagens organizadas por **categorias** (as categorias podem ou não ter imagem associada)
- Existem imagens sem categoria associada
- Pesquisa/filtragem por **nome**, **descrição** e/ou **categoria**

#### Gestão (exclusivo para Administradores)
- CRUD de **categorias** (com upload de imagem opcional)
- CRUD de **imagens de t-shirt** (com upload obrigatório de imagem)
- CRUD de **cores** disponíveis para venda (com upload de imagem da t-shirt base por cor)
- Configuração de **preços** (tabela `prices`)

> ⚠️ A criação/edição de imagens implica sempre **upload** de ficheiro para o servidor.

---

### G3 — Carrinho de Compras

#### Acesso
- Disponível para **todos os utilizadores**, incluindo anónimos.
- O conteúdo **persiste na sessão do servidor**, mesmo após autenticação.

#### Adicionar ao Carrinho
- Integrado no catálogo e na área de imagens personalizadas
- O utilizador define: **quantidade**, **cor** e **tamanho**

#### Interface do Carrinho (por item/linha)
- Imagem (ou preview de estampagem)
- Nome da imagem
- Cor e Tamanho
- Quantidade
- Preço unitário
- Subtotal (com informação de desconto, se aplicável)
- **Total global** de todos os itens

#### Gestão do Carrinho
- Remover item individualmente
- Alterar quantidade, cor e tamanho por item
- Remoção automática ao reduzir quantidade para `0`
- **Limpeza total** do carrinho numa única operação
- Botão de **Checkout** para avançar para a encomenda

---

### G4 — Encomendas

#### Checkout (exclusivo para Clientes autenticados)
- Administradores e funcionários **não** têm acesso ao checkout
- Utilizadores anónimos são redirecionados para login/registo (carrinho preservado)
- O cliente confirma/preenche:
  - **NIF** (pré-preenchido do perfil)
  - **Endereço de entrega** (pré-preenchido do perfil)
  - **Método de pagamento** (Visa / PayPal / MB WAY)
  - **Referência de pagamento**
  - **Notas adicionais** (opcional)
- Pagamento simulado via API externa
- Apenas em caso de **sucesso** → encomenda registada com estado `pending`
- Itens da encomenda replicam integralmente os dados do carrinho (imagens, preços, descontos) — **imutabilidade histórica**

#### Consulta por Perfil

| Perfil | O que pode ver |
|---|---|
| Cliente | Histórico das suas encomendas + detalhes + PDF (só `closed`) |
| Funcionário | Lista de encomendas `pending` + detalhes completos |
| Administrador | Qualquer encomenda, filtrada por estado/cliente/data + detalhes + recibos |

#### Transições de Estado por Perfil

| Perfil | Para `closed` | Para `canceled` |
|---|---|---|
| Funcionário | ✅ (só `pending`) | ❌ |
| Administrador | ✅ (qualquer) | ✅ (qualquer, com razão opcional) |

> 💡 O campo `reason_for_cancellation` é opcional, mas quando preenchido deve estar visível tanto ao administrador como ao cliente no detalhe da encomenda.

---

### G5 — Imagens Personalizadas

#### Acesso e Privacidade
- **Exclusivas** do cliente proprietário
- Acessíveis a funcionários/administradores **apenas para visualização** no contexto de encomendas
- **Proibido** qualquer acesso por utilizadores anónimos ou outros clientes
- Não estão associadas a nenhuma categoria (`category_id` é `null`)
- Têm **preço base diferente** das imagens de catálogo

#### Gestão (exclusiva do Cliente)
- CRUD completo das suas imagens (consulta, adição, atualização, remoção)
- Upload obrigatório de ficheiros para o servidor
- Área **independente** da gestão do catálogo

> ⚠️ Administradores e funcionários **não** têm permissão para gerir ou alterar as imagens personalizadas dos clientes.

---

### G6 — Recibos e E-mail

#### Geração de Recibo PDF
- Gerado automaticamente quando uma encomenda passa para `closed`
- Armazenado em `storage/app/private/pdf_receipts/`
- Conteúdo mínimo do recibo:
  - Logótipo e nome da empresa (logótipo opcional)
  - Dados do cliente (NIF e nome)
  - Data da encomenda
  - Lista detalhada de itens com valores (imagem opcional)
  - Total final
- Acesso exclusivo ao cliente proprietário e aos administradores
- **Vedado** a funcionários, utilizadores anónimos ou outros clientes

#### E-mails Automáticos (via mailtrap.io)

| Evento | Destinatário | Conteúdo |
|---|---|---|
| Encomenda criada (`pending`) | Cliente | Notificação de pedido em processamento |
| Encomenda anulada (`canceled`) | Cliente | Notificação de anulação |
| Encomenda enviada (`closed`) | Cliente | Agradecimento + recibo PDF em anexo |

---

### G7 — Preview de T-Shirts

#### Objetivo
Apresentar uma imagem simulada do aspeto final da t-shirt, combinando uma **cor específica** com um **design** escolhido (sobreposição da imagem sobre a t-shirt base).

#### Onde pode ser utilizado
- Carrinho de compras
- Detalhes da encomenda
- Recibo PDF

#### Implementação
- Sobreposição via **CSS** e/ou **bibliotecas de manipulação de imagens**
- Imagem base = ficheiro por cor em `storage/app/public/tshirt_base/`
- O campo `custom` (JSON) da tabela `tshirt_images` pode ser utilizado para guardar configurações de ajuste (escala, posição, transparência) — **uso opcional**
- São aceites implementações com **diferentes níveis de complexidade**

> 💡 Esta funcionalidade **não é crítica** para o funcionamento base da aplicação. A avaliação considera a qualidade visual e a eficácia da integração.

---

### G8 — Estatísticas

#### Acesso
- Exclusivo para **Administradores**

#### Conteúdo (à responsabilidade dos estudantes)
Exemplos de métricas relevantes:
- Volume total de vendas
- Médias mensais/anuais
- Valores máximos/mínimos (financeiros e em quantidade)
- Dados organizados por: período temporal (mês/ano), categorias, imagens de catálogo, cliente

> 🏆 A avaliação considera: **quantidade** de informação, **relevância** para a gestão do negócio e **qualidade da apresentação**.

---

## 6. Base de Dados

A estrutura é fornecida através de **migrações Laravel** e **não pode ser alterada** sem autorização explícita do docente. Os dados de teste são fornecidos via **database seeders**.

> 🔑 **Todos os utilizadores têm a mesma senha de teste: `123`**

---

### Diagrama ER (Resumo)

```
users ──────── customers ──────── orders ──────── order_items
                    │                                   │
                    └─── tshirt_images ─────────────────┘
                                │
                            categories
                            
prices (tabela de configuração — 1 único registo)
colors (chave primária = código CSS da cor)
```

**Relações principais:**
- `users` 1─1 `customers` (subtabela de clientes)
- `customers` 1─N `orders`
- `orders` 1─N `order_items`
- `order_items` N─1 `tshirt_images`
- `order_items` N─1 `colors`
- `tshirt_images` N─1 `categories` (opcional, `null` se sem categoria)
- `tshirt_images` N─1 `customers` (opcional, `null` se imagem de catálogo)

---

### Tabela: `users`

Tabela central que regista **todos os utilizadores** da plataforma (clientes, funcionários e administradores). Atua como **superclasse** da tabela `customers`.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | INTEGER | ✅ | Chave primária, autoincrementável |
| `name` | VARCHAR | ✅ | Nome completo do utilizador |
| `email` | VARCHAR | ✅ | E-mail único — credencial de acesso |
| `email_verified_at` | DATETIME | ❌ | Gerido internamente pelo Laravel (verificação de e-mail) |
| `password` | VARCHAR | ✅ | Hash da palavra-passe |
| `remember_token` | VARCHAR | ❌ | Gerido internamente pelo Laravel (autenticação) |
| `user_type` | VARCHAR | ✅ | Tipo: `C` = Cliente · `F` = Funcionário · `A` = Administrador |
| `gender` | VARCHAR | ✅ | Género: `M` = Masculino · `F` = Feminino |
| `blocked` | TINYINT | ✅ | `0` = ativo · `1` = bloqueado (impede autenticação) |
| `photo_url` | VARCHAR | ❌ | Caminho relativo da fotografia/avatar do utilizador |
| `custom` | TEXT | ❌ | JSON com dados suplementares (uso opcional) |
| `created_at` | DATETIME | — | Gerido automaticamente pelo Laravel |
| `updated_at` | DATETIME | — | Gerido automaticamente pelo Laravel |
| `deleted_at` | DATETIME | ❌ | Soft delete: `null` = ativo; data = removido |

---

### Tabela: `customers`

Armazena os dados exclusivos dos clientes. É uma **subclasse** de `users` (relação 1-para-1). Os dados genéricos residem em `users`; os dados específicos residem aqui.

> ⚠️ O campo `id` **não é autoincrementável**. Ao criar um cliente, deve-se:
> 1. Inserir primeiro o registo em `users`
> 2. Usar o `id` gerado para criar o registo em `customers`
>
> Na remoção, seguir a **ordem inversa**.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | INTEGER | ✅ | Chave primária **e** chave estrangeira → `users.id` (não autoincrementável) |
| `nif` | VARCHAR | ❌ | NIF com exactamente **9 dígitos**. Pré-preenche o NIF nas encomendas |
| `address` | TEXT | ❌ | Endereço de envio. Valor por omissão para morada nas encomendas |
| `default_payment_type` | VARCHAR | ❌ | Método preferencial: `Visa` · `PayPal` · `MB` |
| `default_payment_ref` | VARCHAR | ❌ | Referência de pagamento preferencial |
| `custom` | TEXT | ❌ | JSON com dados suplementares (uso opcional) |
| `deleted_at` | DATETIME | ❌ | Soft delete |

---

### Tabela: `categories`

Categorias de imagens de t-shirts do catálogo.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | INTEGER | ✅ | Chave primária, autoincrementável |
| `name` | VARCHAR | ✅ | Nome da categoria |
| `image_url` | VARCHAR | ❌ | Caminho relativo da imagem representativa da categoria |
| `custom` | TEXT | ❌ | JSON com dados suplementares (uso opcional) |
| `deleted_at` | DATETIME | ❌ | Soft delete |

> 💡 Imagens de t-shirts podem existir **sem categoria** (`category_id = null`).

---

### Tabela: `prices`

Tabela de configuração dos preços das t-shirts. **Deve conter apenas uma linha** (sem histórico de preços — só a configuração em vigor).

> 💡 Embora a tabela tenha apenas um registo, a chave primária é necessária para integração com os modelos Eloquent do Laravel.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | INTEGER | ✅ | Chave primária, autoincrementável |
| `unit_price_catalog` | NUMERIC | ✅ | Preço unitário — imagens de catálogo (sem desconto) |
| `unit_price_own` | NUMERIC | ✅ | Preço unitário — imagens personalizadas (sem desconto) |
| `unit_price_catalog_discount` | NUMERIC | ✅ | Preço unitário com desconto — imagens de catálogo |
| `unit_price_own_discount` | NUMERIC | ✅ | Preço unitário com desconto — imagens personalizadas |
| `qty_discount` | INTEGER | ✅ | Limiar de quantidade para aplicação do desconto (ex: `10` → desconto a partir de 10 unidades do mesmo item) |
| `custom` | TEXT | ❌ | JSON com dados suplementares (uso opcional) |

**Lógica de aplicação de desconto:**
> O desconto aplica-se quando um item (mesma imagem + cor + tamanho) atinge ou ultrapassa `qty_discount` unidades.

---

### Tabela: `colors`

Define as cores das t-shirts comercializadas. Todos os itens do carrinho e das encomendas devem estar associados a uma cor desta tabela.

> ⚠️ Para cada cor, **deve existir um ficheiro de imagem** da t-shirt base (sem estampagem) em `storage/app/public/tshirt_base/`, cujo nome coincide com a chave primária (`code`).

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `code` | VARCHAR | ✅ | **Chave primária** — código CSS da cor (ex: `"white"`, `"black"`, `"#7fffd4"`) |
| `name` | VARCHAR | ✅ | Nome apresentado ao utilizador (ex: `"Branco"`, `"Azul Marinho"`) |
| `custom` | TEXT | ❌ | JSON com dados suplementares (uso opcional) |
| `deleted_at` | DATETIME | ❌ | Soft delete |

---

### Tabela: `tshirt_images`

Armazena os designs (imagens) das t-shirts — tanto o **catálogo oficial** como as **imagens personalizadas** dos clientes. A distinção é feita pelo campo `customer_id`.

| `customer_id` | Tipo de imagem |
|---|---|
| `null` | Imagem de **catálogo público** |
| referência para `customers.id` | Imagem **privada** de um cliente |

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | INTEGER | ✅ | Chave primária, autoincrementável |
| `customer_id` | INTEGER | ❌ | FK → `customers.id`. `null` = catálogo; preenchido = imagem privada do cliente |
| `category_id` | INTEGER | ❌ | FK → `categories.id`. `null` = sem categoria. **Obrigatoriamente `null`** se `customer_id != null` |
| `name` | VARCHAR | ✅ | Nome da imagem (para organização e filtragem) |
| `description` | TEXT | ❌ | Descrição da imagem (exibida no catálogo) |
| `image_url` | VARCHAR | ✅ | Caminho relativo do ficheiro da imagem de t-shirt |
| `custom` | TEXT | ❌ | JSON com dados de ajuste para preview (escala, posição, transparência) — uso opcional |
| `created_at` | DATETIME | — | Gerido automaticamente pelo Laravel |
| `updated_at` | DATETIME | — | Gerido automaticamente pelo Laravel |
| `deleted_at` | DATETIME | ❌ | Soft delete |

**Regras de negócio:**
- Imagens de catálogo: `customer_id = null`, `category_id` pode ser preenchido ou `null`
- Imagens personalizadas: `customer_id != null`, `category_id` deve ser **sempre** `null`

---

### Tabela: `orders`

Regista as **encomendas** efetuadas. O carrinho mantém-se **exclusivamente na sessão** enquanto está a ser editado — só é criado um registo nesta tabela após confirmação e pagamento bem-sucedido.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | INTEGER | ✅ | Chave primária, autoincrementável |
| `status` | VARCHAR | ✅ | Estado: `pending` · `closed` · `canceled`. Estado inicial: `pending` |
| `customer_id` | INTEGER | ✅ | FK → `customers.id` |
| `date` | DATE | ✅ | Data da encomenda (dia, mês e ano — sem hora) |
| `total_price` | NUMERIC | ✅ | Total da encomenda (soma dos subtotais de todos os itens) |
| `notes` | TEXT | ❌ | Observações introduzidas pelo cliente durante o checkout |
| `reason_for_cancellation` | TEXT | ❌ | Justificação do administrador para anulação (só relevante em `canceled`, mas sempre opcional) |
| `nif` | VARCHAR | ✅ | NIF de faturação (9 dígitos). Pré-preenchido do perfil, editável |
| `address` | TEXT | ✅ | Endereço de entrega. Pré-preenchido do perfil, editável |
| `payment_type` | VARCHAR | ❌ | Método de pagamento utilizado (`Visa` · `PayPal` · `MB WAY`) |
| `payment_ref` | VARCHAR | ❌ | Referência de pagamento. Pré-preenchida do perfil, editável |
| `receipt_url` | VARCHAR | ❌ | Caminho relativo para o PDF do recibo. Gerado ao transitar para `closed` |
| `custom` | TEXT | ❌ | JSON com dados suplementares (uso opcional) |
| `created_at` | DATETIME | — | Gerido automaticamente pelo Laravel |
| `updated_at` | DATETIME | — | Gerido automaticamente pelo Laravel |

---

### Tabela: `order_items`

Itens individuais de uma encomenda. Cada item representa uma combinação única de **imagem + cor + tamanho**, com uma quantidade.

> 💡 Se uma encomenda tiver a mesma imagem e cor mas tamanhos diferentes (ex: M e L), devem ser registados **itens separados** para cada tamanho.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | INTEGER | ✅ | Chave primária, autoincrementável |
| `order_id` | INTEGER | ✅ | FK → `orders.id` |
| `tshirt_image_id` | INTEGER | ✅ | FK → `tshirt_images.id` (design aplicado) |
| `color_code` | VARCHAR | ✅ | FK → `colors.code` (código CSS da cor) |
| `size` | VARCHAR | ✅ | Tamanho: `XS` · `S` · `M` · `L` · `XL` |
| `qty` | INTEGER | ✅ | Quantidade de unidades vendidas neste item |
| `unit_price` | NUMERIC | ✅ | Preço unitário final **no momento da criação** (inclui desconto se aplicável) |
| `sub_total` | NUMERIC | ✅ | Subtotal = `qty × unit_price` |
| `custom` | TEXT | ❌ | JSON com dados suplementares (uso opcional, raramente utilizado) |

**Nota sobre imutabilidade:** Os valores de `unit_price` e `sub_total` são registados no momento do checkout e **não devem ser alterados** por futuras modificações no catálogo de preços.

---

### Convenções e Campos Comuns

Os seguintes campos aplicam-se de forma transversal a várias tabelas:

| Campo | Tipo | Descrição |
|---|---|---|
| `created_at` | DATETIME | Data/hora de criação do registo — gerido automaticamente pelo Laravel |
| `updated_at` | DATETIME | Data/hora da última atualização — gerido automaticamente pelo Laravel |
| `deleted_at` | DATETIME | **Soft delete**: `null` = registo ativo; data = registo "removido" sem exclusão física. Gerido via Traits do Laravel |
| `custom` | TEXT | JSON com dados suplementares opcionais. Na maioria dos casos permanece `null` |

> 💡 O **soft delete** preserva a integridade referencial e o histórico da plataforma, mesmo após a "remoção" de um registo.

---

## 7. Storage — Estrutura de Ficheiros

Durante o **database seeding**, além dos dados, são copiados ficheiros para `storage/app/`:

```
storage/
└── app/
    ├── private/                        ← Não acessível via URL
    │   ├── pdf_receipts/               ← Recibos PDF gerados pela aplicação
    │   └── tshirt_images_private/      ← Imagens personalizadas dos clientes
    │
    └── public/                         ← Acessível após "php artisan storage:link"
        ├── categories/                 ← Imagens das categorias
        ├── photos/                     ← Fotografias e avatars dos utilizadores
        ├── tshirt_base/                ← Imagens de base por cor (nome = code da cor)
        └── tshirt_images/              ← Imagens das t-shirts do catálogo
```

> 💡 Os estudantes têm liberdade para manter esta estrutura ou adaptá-la, adicionando, removendo ou modificando diretórios conforme necessário.

---

## 8. Plataforma de Pagamentos

A aplicação usa um serviço **externo simulado** ("fake") que mimetiza transações financeiras. Não ocorrem pagamentos reais.

**URI do serviço:** `https://ainet-payments-api.vercel.app`

> 💡 Para consumir o serviço, utilizar o **Laravel HTTP Client** ([documentação oficial](https://laravel.com/docs/http-client)).

---

### Endpoint e Payload

```http
POST /api/payments
Content-Type: application/json
```

**Payload (exemplo):**
```json
{
  "type": "Visa",
  "reference": "4563498932456786",
  "value": 17.52
}
```

**Respostas:**

| Código HTTP | Significado |
|---|---|
| `201 Created` | Pagamento processado com sucesso |
| `422 Unprocessable Entity` | Payload inválido, referência não reconhecida ou valor acima do limite |
| Outros | Possíveis erros do serviço |

---

### Validação

As seguintes regras devem ser validadas **tanto na FunShirt como na plataforma de pagamentos**:

| Campo | Regras |
|---|---|
| `type` | Obrigatório. Valores aceites: `"Visa"` · `"PayPal"` · `"MB WAY"` |
| `reference` | Obrigatório. Regras dependem do `type` (ver tabela abaixo) |
| `value` | Obrigatório. Número positivo entre `0.01` e `999999.99`, máximo 2 casas decimais |

**Regras de `reference` por tipo de pagamento:**

| Tipo | Formato | Exemplo |
|---|---|---|
| `Visa` | 16 dígitos iniciados por `4` | `4321567812345678` |
| `PayPal` | E-mail válido | `maria.oliveira@mail.pt` |
| `MB WAY` | 9 dígitos iniciados por `9` | `915785345` |

---

### Simulação de Pagamentos Inválidos

O serviço permite simular falhas de negócio para testar a resiliência da aplicação. As referências seguintes **passam na validação de formato da FunShirt**, mas são rejeitadas pela plataforma externa.

> ⚠️ **Estas regras de simulação NÃO devem ser validadas na FunShirt.** O objetivo é verificar como a aplicação trata respostas de erro da plataforma externa.

#### Conta Inexistente (Referência Inválida)

| Tipo | Condição | Exemplo |
|---|---|---|
| Visa | Começa por `"40"` | `4021567812345678` |
| PayPal | E-mail começa por `"xx"` | `xx.maria@mail.pt` |
| MB WAY | Começa por `"90"` | `901645932` |

#### Saldo Insuficiente (apenas para valores > 20€)

| Tipo | Condição | Exemplo |
|---|---|---|
| Visa | Começa por `"49"` | `4921567812345678` |
| PayPal | E-mail começa por `"zz"` | `zz.maria@mail.pt` |
| MB WAY | Começa por `"99"` | `991645932` |

---

## 9. Entrega

### Submissão no Moodle

| # | Componente | Responsável | Notas |
|---|---|---|---|
| 1 | **Código do Projeto (ZIP)** | 1 elemento do grupo | Excluir: `vendor/`, `node_modules/`, `database/`, `storage/`, `.git/` |
| 2 | **Relatório do Grupo (Excel)** | 1 elemento do grupo | Identificação dos elementos + detalhes técnicos |
| 3 | **Relatório Individual (Excel)** | Cada estudante | Autoavaliação + avaliação entre pares |

### Link Externo

| # | Componente | Requisitos |
|---|---|---|
| 4 | **Projeto Completo (ZIP)** | Link (ex: OneDrive institucional) incluído no relatório do grupo · Link ativo mínimo 1 mês · Excluir `.git/` |

---

## 10. Avaliação

### Pesos por Grupo

| Nº | Peso | Grupo de Funcionalidades |
|---|---|---|
| G1 | **20%** | Autenticação, Perfil e Gestão de Utilizadores |
| G2 | **20%** | Catálogo |
| G3 | **20%** | Carrinho de Compras |
| G4 | **20%** | Encomendas |
| G5 | **5%** | Imagens Personalizadas |
| G6 | **5%** | Recibos e E-mails |
| G7 | **5%** | Preview de T-Shirts |
| G8 | **5%** | Estatísticas |

A classificação de cada grupo considera:
- Quantidade e qualidade das funcionalidades implementadas
- Integração das funcionalidades na aplicação
- Usabilidade e correção (funcionamento correto)
- Aspeto visual (layout, consistência e estrutura)

---

### Requisitos Transversais

#### 🏗️ Arquitetura e Boas Práticas
- Padrão **MVC** e convenções da Framework Laravel
- Métodos HTTP corretos (GET, POST, PUT, DELETE, etc.)
- Rotas RESTful e semânticas

#### ⚙️ Utilização da Framework
- **Eloquent** (em vez de `DB::`) para acesso a dados
- **Form Requests** para validações
- Sistema de **autenticação e Hash** nativos do Laravel
- **Policies** para controlo de autorização

#### 🔁 Princípio DRY (Don't Repeat Yourself)
- Vistas parciais (partials)
- Componentes Blade
- Centralização de lógica comum nos modelos
- Incentivada a utilização de outros padrões e boas práticas da framework

#### 🚀 Desempenho e Eficiência
- Redução do número de queries à base de dados
- Extração seletiva (apenas colunas e registos necessários)
- Uso de **Queues** para tarefas assíncronas (ex: envio de e-mails)
- Uso de **Cache** onde aplicável
- Minimização do payload das respostas HTTP

#### 🔒 Segurança e Privacidade
- Todos os recursos protegidos por **autenticação e autorização**
- Seguir as diretrizes de segurança nativas do Laravel
- Garantir confidencialidade e privacidade dos dados
- Mitigar vulnerabilidades e impedir acessos indevidos

> 📝 **Nota:** O projeto foca-se na camada aplicacional — **NÃO** é necessário HTTPS.

---

*Documento gerado com base no enunciado oficial do Projeto FunShirt — EI · AI 2025/26*
