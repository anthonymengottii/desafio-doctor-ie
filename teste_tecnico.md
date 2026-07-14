# Teste Técnico — Full Stack (Flutter + Laravel)

## Objetivo

Construir um sistema completo (**backend + frontend**) para gestão de livros e seus índices.

---

## Contexto

Você deverá desenvolver um sistema onde usuários autenticados podem cadastrar livros e seus índices (sumário). Os usuários também devem conseguir identificar livros semanticamente equivalentes.

### Cada livro possui:

- Título
- Usuário publicador (usuário logado)
- Número de páginas
- Índices (estrutura hierárquica ilimitada)

### Cada índice possui:

- Índice pai
- Título
- Página

Um índice pode possuir subíndices (estrutura recursiva).

---

# Requisitos Funcionais

## Autenticação

- Criar endpoint de autenticação (token)
- Todas as rotas devem estar protegidas

---

## Livros

### Criar livro

O usuário deve conseguir cadastrar um livro contendo:

- Título
- Usuário publicador
- Número de páginas
- Estrutura completa de índices

### Exemplo de payload (`POST /books`)

```json
{
  "titulo": "Clean Code",
  "numero_paginas": 450,
  "indices": [
    {
      "titulo": "Capítulo 1",
      "pagina": 1,
      "subindices": [
        {
          "titulo": "Introdução",
          "pagina": 2,
          "subindices": []
        }
      ]
    }
  ]
}
```

---

### Listar livros

O usuário deve conseguir listar livros utilizando filtros:

- `titulo`: filtro por título
- `titulo_do_indice`: retorna livros com índice correspondente e seus ascendentes

### Exemplo de resposta (`GET /books`)

```json
[
  {
    "id": 1,
    "titulo": "Clean Code",
    "usuario_publicador": {
      "id": 1,
      "nome": "Bill"
    },
    "numero_paginas": 450,
    "indices": [
      {
        "titulo": "Capítulo 1",
        "pagina": 1,
        "subindices": [
          {
            "titulo": "Introdução",
            "pagina": 2,
            "subindices": []
          }
        ]
      }
    ]
  }
]
```

---

### Editar livro

O usuário deve conseguir editar um livro, incluindo seus índices.

A edição deve permitir:

- Alterar título
- Alterar número de páginas
- Adicionar novos índices
- Editar índices existentes
- Remover índices (incluindo seus subíndices)

---

### Deletar livro

O usuário deve conseguir remover um livro.

Ao excluir um livro, todos os seus índices também devem ser removidos.

---

# Similaridade Semântica de Títulos

A equivalência semântica é a relação em que duas expressões ou fragmentos de texto transmitem o mesmo significado ou conceito, mesmo utilizando palavras diferentes.

O sistema deve permitir listar livros e identificar aqueles semanticamente equivalentes.

### Requisitos

- Ignorar acentuação
- Não diferenciar letras maiúsculas e minúsculas (case insensitive)
- Funcionar com milhares de registros
- Considerar dois livros semanticamente similares quando seus títulos possuírem similaridade

---

# Frontend

Desenvolver um aplicativo simples para gerenciamento de livros.

O aplicativo deve possuir as seguintes telas:

- Listagem de livros com filtros
- Detalhes do livro (incluindo seus índices)
- Cadastro de livros
- Edição de livros
- Exclusão de livros
- Listagem de livros com títulos semanticamente similares

---

# Requisitos Técnicos

## Backend

- Laravel

## Frontend

- Flutter

## Banco de Dados

- PostgreSQL **ou** MySQL

## API

- REST

---

# Entregáveis

- Código-fonte do backend
- Código-fonte do frontend
- README contendo as decisões técnicas adotadas
- Repositório Git disponibilizado para avaliação