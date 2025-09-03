<!DOCTYPE html>
<html>
<head>
    <title>Test Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <h2>Test Empresa Form</h2>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    
        <form method="POST" action="{{ route('ajustes.empresa') }}">
        @csrf
        <div class="mb-3">
            <label>Razón Social</label>
            <input type="text" class="form-control" name="razon_social" value="Test Company" required>
        </div>
        <div class="mb-3">
            <label>RFC</label>
            <input type="text" class="form-control" name="rfc" value="TCO1234567XY" maxlength="13" required>
        </div>
        <div class="mb-3">
            <label>Calle y Número</label>
            <input type="text" class="form-control" name="calle_numero" value="Test Street 123" required>
        </div>
        <div class="mb-3">
            <label>Colonia</label>
            <input type="text" class="form-control" name="colonia" value="Test Colonia" required>
        </div>
        <div class="mb-3">
            <label>Ciudad</label>
            <input type="text" class="form-control" name="ciudad" value="Test City" required>
        </div>
        <div class="mb-3">
            <label>Estado</label>
            <select class="form-control" name="estado" required>
                <option value="Ciudad de México">Ciudad de México</option>
            </select>
        </div>
        <div class="mb-3">
            <label>País</label>
            <input type="text" class="form-control" name="pais" value="México" required>
        </div>
        <div class="mb-3">
            <label>Código Postal</label>
            <input type="text" class="form-control" name="codigo_postal" value="12345" maxlength="5" required>
        </div>
        <div class="mb-3">
            <label>Teléfono</label>
            <input type="tel" class="form-control" name="telefono" value="5551234567">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="test@company.com">
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
    
    <hr>
    <h3>Datos actuales:</h3>
    <pre>{{ json_encode(App\Models\Ajuste::where('nombre', 'like', 'empresa_%')->get(['nombre', 'valor']), JSON_PRETTY_PRINT) }}</pre>
    </div>
</body>
</html>
</body>
</html>
