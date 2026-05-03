<!DOCTYPE html>
<html lang="da">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log ind — SlopHub</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, "Segoe UI", Roboto, sans-serif; background: #000; color: #1c1e21; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
  .box { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px 28px; width: 100%; max-width: 400px; }
  .logo { text-align: center; margin-bottom: 22px; }
  .logo img { max-width: 220px; width: 100%; height: auto; }
  h1 { font-size: 18px; margin-bottom: 16px; text-align: center; font-weight: 600; color: #65676b; }
  label { display: block; font-weight: 600; font-size: 13px; margin: 10px 0 4px; }
  input[type=email], input[type=password] { width: 100%; padding: 10px 12px; border: 1px solid #dadde1; border-radius: 8px; font-size: 15px; font-family: inherit; }
  input:focus { outline: none; border-color: #1877f2; }
  .btn { width: 100%; margin-top: 16px; padding: 12px; background: #1877f2; color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; }
  .btn:hover { background: #166fe5; }
  .err { background: #fee2e2; color: #b91c1c; padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 10px; }
  .rem { display: flex; align-items: center; gap: 8px; margin-top: 10px; font-size: 13px; color: #65676b; }
  .foot { margin-top: 18px; text-align: center; font-size: 12px; color: #65676b; }
</style>
</head>
<body>
<form class="box" method="POST" action="{{ url('/slophub/login') }}">
  @csrf
  <div class="logo"><img src="{{ url('/img/slophub-logo.png') }}" alt="SlopHub"></div>
  <h1>Log ind</h1>
  @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
  <label>Email</label>
  <input type="email" name="email" value="{{ old('email') }}" required autofocus>
  <label>Kodeord</label>
  <input type="password" name="password" required>
  <label class="rem"><input type="checkbox" name="remember" value="1"> Husk mig</label>
  <button class="btn">Log ind</button>
  <div class="foot">Ingen konto? Du skal bruge et invitationslink fra din underviser.</div>
</form>
</body>
</html>
