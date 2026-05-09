<!DOCTYPE html>
<html lang="da">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log ind — Simbuktu</title>
<link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, "Segoe UI", Roboto, sans-serif; background: #000; color: #1c1e21; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
  .box { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 36px 32px; width: 100%; max-width: 440px; }
  .logo { text-align: center; margin-bottom: 24px; }
  .logo img { max-width: 240px; width: 100%; height: auto; }
  h1 { font-size: 20px; margin-bottom: 20px; text-align: center; font-weight: 600; color: #65676b; }
  label { display: block; font-weight: 600; font-size: 14px; margin: 14px 0 6px; }
  input[type=email], input[type=password] { width: 100%; padding: 14px 14px; border: 1px solid #dadde1; border-radius: 8px; font-size: 16px; font-family: inherit; }
  input:focus { outline: none; border-color: #1877f2; }
  .btn { width: 100%; margin-top: 20px; padding: 16px; background: #1877f2; color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: 17px; cursor: pointer; }
  .btn:hover { background: #166fe5; }
  .err { background: #fee2e2; color: #b91c1c; padding: 12px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 12px; }
  .rem { display: flex; align-items: center; gap: 8px; margin-top: 14px; font-size: 14px; color: #65676b; }
  .rem input { width: 18px; height: 18px; }
  .foot { margin-top: 20px; text-align: center; font-size: 13px; color: #65676b; }
  @media (max-width: 480px) {
    body { padding: 12px; }
    .box { padding: 28px 22px; border-radius: 10px; }
  }
</style>
</head>
<body>
<form class="box" method="POST" action="{{ url('/simulation/login') }}">
  @csrf
  <div class="logo"><img src="{{ asset('img/simbuktu-logo.png') }}" alt="Simbuktu"></div>
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
