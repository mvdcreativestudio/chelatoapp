@extends('layouts/layoutMaster')

@section('content')
<div class="container mt-5">
    <h1>Editar Plantilla de Correo</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('email-templates.update', $template->id) }}" method="POST" id="template-form">
        @csrf
        <div class="form-group">
            <label for="template_selector">Seleccionar Plantilla</label>
            <select id="template_selector" name="template_id" class="form-control" onchange="location = this.value;">
                @foreach($templates as $tpl)
                    <option value="{{ route('email-templates.edit', $tpl->id) }}" {{ $template->id == $tpl->id ? 'selected' : '' }}>
                        {{ $tpl->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="template_subject">Asunto del Correo</label>
            <input type="text" id="template_subject" name="template_subject" class="form-control" value="{{ old('template_subject', $template->subject) }}">
        </div>
        <div class="form-group">
            <label for="template_body">Contenido de la Plantilla</label>
            <div id="editor">{!! old('template_body', $template->body) !!}</div>
            <input type="hidden" id="template_body" name="template_body" value="{{ old('template_body', $template->body) }}">
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
    </form>

    <h3>Variables Disponibles</h3>
    <ul>
        @foreach(config('email_templates.variables') as $variable => $description)
            <li><strong>&#123;&#123; {{ $variable }} &#125;&#125;</strong> - {{ $description }}</li>
        @endforeach
    </ul>
</div>

<!-- Quill JS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<!-- Image Resize Module -->
<script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
<script>
    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
        ['blockquote', 'code-block'],

        [{ 'header': 1 }, { 'header': 2 }],               // custom button values
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
        [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
        [{ 'direction': 'rtl' }],                         // text direction

        [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

        [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
        [{ 'font': [] }],
        [{ 'align': [] }],

        ['clean'],                                         // remove formatting button
        ['link', 'image', 'video']                        // link and image, video
    ];

    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: toolbarOptions,
            imageResize: {
                modules: [ 'Resize', 'DisplaySize', 'Toolbar' ]
            }
        }
    });

    var form = document.getElementById('template-form');
    form.onsubmit = function() {
        var templateBody = document.querySelector('input[name=template_body]');
        templateBody.value = quill.root.innerHTML;
    };
</script>
@endsection
