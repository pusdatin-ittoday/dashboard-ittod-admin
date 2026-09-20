<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="0;url={{ route('login') }}">
    <title>Sesi Telah Berakhir</title>
    <script>
        window.location.href = "{{ route('login') }}";
    </script>
</head>
<body style="font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 1rem;">
    <div style="background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 24rem; width: 100%; text-align: center;">
        <h1 style="font-size: 1.125rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Sesi Telah Berakhir</h1>
        <p style="font-size: 0.875rem; color: #4b5563; margin-bottom: 1.25rem;">Mengalihkan ke halaman login...</p>
        <a href="{{ route('login') }}" style="display: inline-block; padding: 0.5rem 1rem; background-color: #4f46e5; color: white; border-radius: 0.375rem; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
            Masuk Kembali
        </a>
    </div>
</body>
</html>
