# Infinity Tech · Sistema de Estoque e Orçamentos (SA mvc)

Sistema web para gestão de orçamentos e estoque de peças de uma empresa de manutenção de ar-condicionado ("Infinity Tech"), com login separado para **clientes** e **funcionários**, geração de orçamento em **PDF** (via Dompdf) e persistência em **MySQL/MariaDB**.

> Documentação gerada a partir da análise do código-fonte (`SA_mvc__2_.zip`) e do dump do banco (`infinity_tech .sql`).

---

## 1. Stack técnica

| Camada         | Tecnologia                                  |
|----------------|----------------------------------------------|
| Linguagem      | PHP 8.2                                       |
| Banco de dados | MariaDB 10.4 (via PDO)                        |
| PDF            | [Dompdf](https://github.com/dompdf/dompdf) v3.1.5 |
| Dependências   | Composer (`composer.json` / `composer.lock`)  |
| Front-end      | HTML + CSS puro (sem framework), JS vanilla   |

---

## 2. Estrutura de pastas

```
SA mvc/
├── index.php                 → Login (identifica cliente x funcionário e redireciona)
├── logout.php                → Encerra sessão
├── painel.php                → Painel do funcionário (estoque de peças)
├── painel_cliente.php        → Dashboard do cliente
├── criar_orcamento.php       → Formulário de criação de orçamento (funcionário)
├── gerar_pdf.php             → Processa o formulário, grava no banco e gera o PDF
├── model/
│   └── conexao.php           → Conexão PDO com o MySQL
├── view/
│   └── cadastro.php          → Formulário de cadastro de novo usuário
├── controller/
│   └── cadastrar.php         → Processa o cadastro
├── composer.json / .lock     → Dependência do Dompdf
└── vendor/                   → Bibliotecas instaladas via Composer
```

O projeto segue um **MVC parcial**: só o fluxo de cadastro está de fato separado em `view/` e `controller/`; as demais páginas (login, painéis, orçamento, PDF) misturam lógica PHP e HTML no mesmo arquivo.

---

## 3. Banco de dados (`infinity_tech`)

### Diagrama lógico

```
usuarios (id) ─┬──< orcamentos (id_cliente)
               │         │
               │         └──< orcamento_itens (id_orcamento)
               │                     │
produtos (idProdutos) ──────────────┘

clientes  (tabela isolada, sem uso no código atual)
```

### Tabelas

**`usuarios`** — login de clientes e funcionários
| Coluna    | Tipo         | Observação                                   |
|-----------|--------------|-----------------------------------------------|
| id        | int, PK, AI  |                                                 |
| nome      | varchar(100) |                                                 |
| usuario   | varchar(50)  | UNIQUE                                         |
| senha     | varchar(255) | bcrypt (`$2y$...`) — mas há registro em texto puro (ver seção 5) |
| tipo      | varchar(20)  | esperado `'cliente'` ou `'funcionario'` — **mas há um registro com `'admin'`** (ver seção 5) |

**`produtos`** — estoque de peças/materiais
| Coluna        | Tipo          |
|---------------|---------------|
| idProdutos    | int, PK, AI   |
| Nome_produto  | varchar(255)  |
| Modelo        | varchar(255)  |
| Marca         | varchar(255)  |
| Preco         | decimal(10,2) |

7 peças cadastradas (gás refrigerante, tubo de cobre, capacitor, placa universal, motor ventilador, isolamento térmico, fita PVC).

**`orcamentos`** — cabeçalho de cada orçamento gerado
| Coluna           | Tipo                                   |
|------------------|-----------------------------------------|
| id               | int, PK, AI                             |
| id_cliente       | int, FK → usuarios(id)                  |
| diagnostico      | text                                     |
| taxa_deslocacao  | decimal(10,2), default 0.00             |
| taxa_servico     | decimal(10,2), default 0.00             |
| valor_total      | decimal(10,2)                            |
| status           | enum('Pendente','Aprovado','Rejeitado'), default 'Pendente' |
| data_criacao     | datetime, default `current_timestamp()` |

**`orcamento_itens`** — peças usadas em cada orçamento
| Coluna          | Tipo                                     |
|-----------------|--------------------------------------------|
| id              | int, PK, AI                                |
| id_orcamento    | int, FK → orcamentos(id) `ON DELETE CASCADE` |
| id_produto      | int, FK → produtos(idProdutos) `ON DELETE CASCADE` |
| quantidade      | int                                          |
| preco_unitario  | decimal(10,2)                                |

**`clientes`** — tabela com `email`, `telefone`, `documento`, **não referenciada em nenhum lugar do código**. Parece ter sido criada para um cadastro de cliente mais completo (dados de contato) que acabou não sendo implementado — hoje todo cliente é só uma linha em `usuarios`.

---

## 4. Fluxo da aplicação

1. **Cadastro** (`view/cadastro.php` → `controller/cadastrar.php`): usuário escolhe "Sou Cliente" ou "Sou Funcionário", senha vai com hash bcrypt para `usuarios`.
2. **Login** (`index.php`): autentica por `usuario`/`senha` e redireciona por `tipo`:
   - `funcionario` → `painel.php`
   - qualquer outro valor → `painel_cliente.php`
3. **Painel do funcionário** (`painel.php`): lista `produtos`, permite excluir peça, dá acesso a "Novo Orçamento".
4. **Criar orçamento** (`criar_orcamento.php`): funcionário escolhe cliente, descreve o diagnóstico, define taxa de visita/mão de obra e adiciona peças do estoque com quantidade.
5. **Gerar PDF** (`gerar_pdf.php`): grava o orçamento em `orcamentos` + `orcamento_itens` e renderiza um PDF (Dompdf) com os dados, incluindo chave Pix fixa para pagamento.
6. **Painel do cliente** (`painel_cliente.php`): tela de dashboard com cards de orçamentos (hoje sempre zerados — ver seção 6) e histórico (hoje sempre vazio).

---

## 5. Inconsistências entre o banco e o código (achados na análise)

| # | Achado | Onde | Risco |
|---|--------|------|-------|
| 1 | Usuário `admin` (id 2) tem `tipo = 'admin'`, mas todo o código só reconhece `'funcionario'` ou trata como `'cliente'`. Esse usuário hoje **cai no painel de cliente**, não no painel administrativo. | `usuarios`, `index.php`, `painel.php` | Funcional — a conta "admin" está efetivamente sem função especial nenhuma no sistema atual |
| 2 | Usuário `teste` (id 3) tem senha em **texto puro** (`123456`), não hash. O login (`index.php`) já trata esse caso e faz upgrade para bcrypt no primeiro acesso bem-sucedido, então não quebra — mas é um lembrete de que existem contas legadas sem hash. | `usuarios`, `index.php` | Baixo (mitigado no código), mas exposto se o banco vazar antes do primeiro login |
| 3 | Dois usuários (`Ju`, id 6, e `Weiss`, id 9) foram cadastrados como **`funcionario`** direto pelo formulário público de cadastro — **confirma na prática** a falha de escalação de privilégio já identificada no código (o rádio "Sou Funcionário" não tem nenhuma validação/aprovação). | `controller/cadastrar.php` | **Crítico** |
| 4 | Tabela `clientes` (email, telefone, documento) existe no banco mas **nenhuma tela grava ou lê dela**. | schema | Nenhum risco, mas é código morto / feature incompleta |
| 5 | `orcamentos.status` (Pendente/Aprovado/Rejeitado) existe e já tem 1 registro `'Pendente'`, mas nada no código altera esse status nem o exibe — o dashboard do cliente ignora a tabela e mostra `0` fixo em tudo. | `painel_cliente.php` | Funcional — dashboard do cliente está desconectado do banco |
| 6 | Padrão de nomenclatura inconsistente: `produtos` usa PascalCase (`Nome_produto`, `Preco`, `Marca`, `idProdutos`) enquanto as demais tabelas usam snake_case. | schema | Estético, mas facilita erro de digitação em novas queries |

---

## 6. Pontos de atenção de segurança (revisão de código)

1. **Escalação de privilégio no cadastro** — o campo `tipo` do formulário de cadastro é aceito sem qualquer aprovação; já existem 2 contas reais criadas assim como "funcionário" (ver item 3 da tabela acima). Recomendação: cadastro público sempre cria `cliente`; contas `funcionario` só via convite/admin.
2. **Preço de peça manipulável no orçamento** — `gerar_pdf.php` usa o preço vindo do `<option value="id|preco">` do formulário, sem reconsultar `produtos.Preco` no banco. Recomendação: buscar o preço sempre pelo `id_produto` no servidor.
3. **Exclusão de peça via link GET, sem CSRF** — `painel.php?acao=excluir&id=...` executa `DELETE` só com um `GET`, protegido apenas por um `confirm()` de JavaScript (contornável). Recomendação: usar POST + token CSRF.
4. **Credenciais do banco em texto plano no código** (`model/conexao.php`) — aceitável em ambiente local de desenvolvimento, mas não deve subir para repositório público ou produção sem variáveis de ambiente.

---

## 7. Como rodar localmente

1. Instale um ambiente PHP + MySQL (XAMPP, Laragon, WAMP ou similar) com PHP 8.2+.
2. Importe o dump `infinity_tech (1).sql` em um banco chamado `infinity_tech`.
3. Ajuste `model/conexao.php` se seu usuário/senha do MySQL forem diferentes de `root` / (vazio).
4. Rode `composer install` na pasta do projeto (necessário para o Dompdf, caso a pasta `vendor/` não esteja presente).
5. Acesse `index.php` pelo seu servidor local (ex: `http://localhost/SA%20mvc/index.php`).

**Contas de teste já existentes no dump:**
- Cliente: `João_boaventura`, `geodiva15`, `joao-silva`, `Ju2`, `geo` (senhas com hash — desconhecidas)
- Funcionário: `Ju`, `Weiss` (senhas com hash — desconhecidas)
- Conta legada: `teste` / senha `123456` (texto puro, tipo `cliente`)
- Conta `admin` / `tipo = 'admin'` (hoje sem tratamento especial no código — ver seção 5, item 1)

---

## 8. Sugestões de próximos passos

1. Corrigir a escalação de privilégio no cadastro (prioridade máxima).
2. Buscar o preço da peça no banco em `gerar_pdf.php`, não confiar no POST.
3. Trocar a exclusão de peça (GET) por um formulário POST com token CSRF.
4. Decidir o que fazer com `tipo = 'admin'`: unificar com `'funcionario'` ou criar de fato um terceiro nível de acesso.
5. Conectar `painel_cliente.php` às tabelas `orcamentos`/`orcamento_itens` reais (hoje os números são fixos em zero).
6. Decidir se a tabela `clientes` será usada (cadastro de contato) ou removida do schema.
7. Implementar as ações hoje com `href="#"`: editar peça, cadastrar peça, "Meus Orçamentos", "Meu Perfil".
