Scaffolder (scripts/scaffold_module.php)

Usage (interactive):

  php scripts/scaffold_module.php ModuleName

Usage (non-interactive):

  php scripts/scaffold_module.php ModuleName --fields-file=scripts/samples/example_fields.json --yes

Flags:
  --storage=api|db    Choose storage strategy (default: api).
  --fields-file=path  JSON file containing an array of objects: [{"name":"field","type":"string"}, ...]
  --yes, -y           Non-interactive mode: accept defaults and skip prompts.

Fields file schema example (scripts/samples/example_fields.json):
[
  { "name": "codigo", "type": "string" },
  { "name": "nombres", "type": "string" },
  { "name": "apellidos", "type": "string" },
  { "name": "fecha_nacimiento", "type": "date" },
  { "name": "edad", "type": "number" }
]

Notes:
- The scaffolder generates Controller, Model, View, and JS files in the project.
- Generated modules are DB-independent by default (storage=api). Adjust templates or pass --storage=db if you add DB-backed templates.
- Templates are tokenized; edit files in scripts/templates/module to change generated output.
- The scaffolder will not overwrite existing files; it skips them.
