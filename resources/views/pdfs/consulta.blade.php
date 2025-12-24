<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
        .footer { position: fixed; bottom: 0; text-align: center; border-top: 1px solid #ccc; width: 100%; padding-top: 10px; }
        .section { margin-top: 20px; }
        .label { font-weight: bold; color: #4b5563; }
        .box { border: 1px solid #e5e7eb; padding: 15px; border-radius: 8px; background: #f9fafb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HISTORIA CLÍNICA DIGITAL</h1>
        <p>Registro de Evolución Médica</p>
    </div>

    <div class="section">
        <p><span class="label">Paciente:</span> {{ $paciente->last_name }}, {{ $paciente->first_name }}</p>
        <p><span class="label">DNI:</span> {{ $paciente->dni }} | <span class="label">Fecha:</span> {{ $fecha }}</p>
    </div>

    <div class="section">
        <div class="label">Motivo de Consulta:</div>
        <p>{{ $consulta->reason }}</p>
    </div>

    <div class="section">
        <div class="label">Diagnóstico / Evolución:</div>
        <div class="box">{{ $consulta->diagnosis }}</div>
    </div>

    <div class="section">
        <div class="label">Tratamiento Indicado:</div>
        <div class="box" style="color: #1d4ed8;">{{ $consulta->treatment }}</div>
    </div>

    <div class="footer">
        <p><strong>Dr. {{ $medico->name }}</strong></p>
        <p>Especialidad: {{ $medico->specialty }}</p>
        <p>M.N.: {{ $medico->mn_number }} | M.P.: {{ $medico->mp_number }}</p>
    </div>
</body>
</html>