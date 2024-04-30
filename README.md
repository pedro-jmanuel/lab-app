# Desafio LAB-APP

API para registro de escolas.

### Funcionalidades

* Criar escola;
* Ver escola;
* Listar todas as escola;
* Atualizar escola;
* Eliminar escola;
* Fazer o carregamento em massa das escola usando Excel;
* Listar todas provincias desponiveis;
* Consumir listas de provincia disponiveis em uma API externa:

### Como testar

* Link para teste online: [https://uan.uan.co.ao/](https://uan.uan.co.ao/)
* Arquivo de teste para carregamento em massa pode ser encontrado em:  `lab-app/tests/excel_para_teste.xlsx`
* NOTA: API externa com dados da provincias deve poder retornar uma lista no formato abaixo:

```json
[
  {
    "id": "1",
    "descricao": "Bié",
  },
  {
    "id": "2",
    "descricao": "Bengo",
  }
]
```

### License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
