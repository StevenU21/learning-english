<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Académico del Estudiante</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #222;
            margin: 0;
            padding: 0 40px;
            line-height: 1.6;
        }

        /* ENCABEZADO INSTITUCIONAL */
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #4f46e5;
            padding: 18px 0;
            margin-bottom: 32px;
        }

        .logo {
            width: 70px;
            height: 70px;
            margin-right: 20px;
        }

        .header-info {
            flex-grow: 1;
        }

        .app-title {
            font-size: 1.8em;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 4px;
        }

        .app-meta {
            font-size: 1em;
            color: #555;
        }

        .report-title {
            text-align: center;
            font-size: 1.4em;
            font-weight: bold;
            color: #222;
            margin: 30px 0 20px 0;
            text-transform: uppercase;
        }

        /* BLOQUE DE INFORMACIÓN DEL ESTUDIANTE */
        .student-block {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #f9fafb;
        }

        .avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 2px solid #4f46e5;
            margin-right: 22px;
            object-fit: cover;
        }

        .student-info p {
            margin: 3px 0;
            font-size: 0.98em;
        }

        /* SECCIONES DE CONTENIDO */
        .section-title {
            font-size: 1.15em;
            color: #4f46e5;
            margin: 22px 0 10px 0;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
        }

        ul {
            font-size: 1.02em;
            margin: 0 0 18px 16px;
        }

        ul li {
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 7px;
            text-align: left;
        }

        th {
            background: #f4f6f9;
            font-weight: bold;
            color: #333;
        }

        .observations {
            margin-top: 30px;
            padding: 14px 18px;
            border-left: 4px solid #4f46e5;
            background-color: #f8fafc;
            font-size: 1em;
        }

        .footer {
            margin-top: 40px;
            font-size: 0.95em;
            color: #666;
            text-align: right;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('img/logo03.png') }}" class="logo" alt="Logo institucional">
        <div class="header-info">
            <div class="app-title">Nativo English</div>
            <div class="app-meta">Departamento Académico - Reporte de Progreso Estudiantil</div>
        </div>
    </div>

    <div class="report-title">Reporte Académico del Estudiante</div>

    <div class="student-block">
        @if (!empty($user['avatar_url']))
            <img src="{{ $user['avatar_url'] }}" class="avatar" alt="Foto del estudiante">
        @else
            <img src="{{ public_path('img/logo03.png') }}" class="avatar" alt="Foto del estudiante">
        @endif

        <div class="student-info">
            <p><strong>Nombre:</strong> {{ $user['name'] ?? '' }}</p>
            <p><strong>Email:</strong> {{ $user['email'] ?? '' }}</p>
            <p><strong>Nickname:</strong> {{ $user['profile']['nickname'] ?? '-' }}</p>
            <p><strong>Fecha de nacimiento:</strong> {{ $user['profile']['birthdate'] ?? '-' }}</p>
            <p><strong>Nivel académico:</strong> {{ $user['profile']['academic_level'] ?? '-' }}</p>
            <p><strong>Género:</strong> {{ $user['profile']['gender'] ?? '-' }}</p>
        </div>
    </div>

    <div class="section-title">Resumen de Desempeño Académico</div>
    <ul>
        <li><strong>Total de unidades trabajadas:</strong> {{ $unitProgress->count() }}</li>
        <li><strong>Total de lecciones completadas:</strong> {{ $lessonProgress->count() }}</li>
        <li><strong>Total de ejercicios realizados:</strong> {{ $attempts->count() }}</li>
        <li><strong>Promedio de avance por unidad:</strong>
            {{ $unitProgress->count() ? number_format($unitProgress->avg('progress'), 1) : 0 }}%</li>
        <li><strong>Promedio de avance por lección:</strong>
            {{ $lessonProgress->count() ? number_format($lessonProgress->avg('progress'), 1) : 0 }}%</li>
    </ul>

    <div class="observations">
        <strong>Observaciones:</strong><br>
        El presente reporte refleja el avance académico del estudiante dentro de la plataforma <em>Nativo English</em>.
        Los datos aquí expuestos han sido generados automáticamente a partir de su actividad académica en las unidades,
        lecciones y ejercicios asignados. Para un seguimiento personalizado, se recomienda revisar el sistema y
        consultar
        con el tutor académico correspondiente.
    </div>

    <div class="footer">
        Generado por: {{ auth()->user()->name ?? 'Administrador del sistema' }}<br>
        Fecha de emisión: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
