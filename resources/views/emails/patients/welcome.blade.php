<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido/a a Clínica Norte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #1d4ed8;
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            color: #bfdbfe;
            margin: 6px 0 0;
            font-size: 14px;
        }
        .body {
            padding: 36px 40px;
            color: #374151;
            font-size: 15px;
            line-height: 1.7;
        }
        .body p {
            margin: 0 0 16px;
        }
        .credentials-box {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 20px 24px;
            margin: 24px 0;
        }
        .credentials-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .credentials-box td {
            padding: 6px 0;
            font-size: 15px;
        }
        .credentials-box td:first-child {
            color: #6b7280;
            width: 160px;
            font-weight: 600;
        }
        .credentials-box td:last-child {
            color: #111827;
            font-family: 'Courier New', monospace;
            font-weight: 700;
        }
        .btn {
            display: inline-block;
            background-color: #1d4ed8;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            margin: 8px 0 24px;
        }
        .notice {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            font-size: 13px;
            color: #92400e;
            border-radius: 0 4px 4px 0;
            margin-bottom: 24px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Clínica Norte</h1>
            <p>Bienvenido/a al sistema de pacientes</p>
        </div>

        <div class="body">
            <p>Estimado/a <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>,</p>

            <p>Su registro en <strong>Clínica Norte</strong> ha sido completado. A continuación encontrará sus credenciales de acceso al portal de pacientes:</p>

            <div class="credentials-box">
                <table>
                    <tr>
                        <td>Usuario (CI):</td>
                        <td>{{ $patient->ci }}</td>
                    </tr>
                    <tr>
                        <td>Correo:</td>
                        <td>{{ $patient->email }}</td>
                    </tr>
                    <tr>
                        <td>Contraseña:</td>
                        <td>{{ $tempPassword }}</td>
                    </tr>
                </table>
            </div>

            <p style="text-align:center;">
                <a href="{{ $portalUrl }}" class="btn">Acceder al portal</a>
            </p>

            <div class="notice">
                <strong>Importante:</strong> Le recomendamos cambiar su contraseña después de iniciar sesión por primera vez.
            </div>

            <p>Si tiene alguna consulta, puede comunicarse con nosotros directamente en la clínica.</p>

            <p>Atentamente,<br><strong>Clínica Norte</strong></p>
        </div>

        <div class="footer">
            Este correo fue generado automáticamente, por favor no responda a este mensaje.
        </div>
    </div>
</body>
</html>
