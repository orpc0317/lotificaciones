<!-- Template view for {{Name}} module -->
<div class="card">
  <div class="card-body">
    <h3>{{Name}} Module</h3>
    <div id="{{name}}-app">This is a scaffolded module. Edit templates as required.</div>
    <hr>
    <!-- Trigger modal -->
    <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modal{{Name}}">Open {{Name}} Form</button>

    <!-- Modal: photo left, fields right -->
    <div class="modal fade" id="modal{{Name}}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{Name}}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="modal-split-row d-grid" style="grid-template-columns: 30% 1fr; gap:1rem; align-items:start;">
              <!-- Photo card (left) -->
              <div class="card">
                <div class="card-body text-center">
                  <div class="mb-2">
                    <img id="{{name}}_photo_preview" src="/public/assets/images/avatar.png" alt="photo" class="img-fluid rounded" style="max-height:220px; object-fit:cover; width:100%;">
                  </div>
                  <div class="small text-muted">Photo preview</div>
                  <div class="mt-2" data-include-upload="{{include_upload}}">
                    <input type="file" id="{{name}}_file" name="file" class="form-control form-control-sm">
                  </div>
                </div>
              </div>

              <!-- Fields card (right) -->
              <div class="card">
                <div class="card-body">
                  <form id="{{name}}-form">
                    {{form_inputs}}
                    <div class="mt-3 d-flex justify-content-end">
                      <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <hr>
    <table id="tabla{{Name}}" class="table table-striped" style="width:100%"></table>
  </div>
</div>
