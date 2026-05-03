<!DOCTYPE html>
<html lang="da">
<head>
<meta charset="UTF-8">
<title>Tilmeld dig {{ $course->name }} — SlopHub</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, "Segoe UI", Roboto, sans-serif; background: #000; color: #1c1e21; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
  .box { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px 28px; width: 100%; max-width: 440px; }
  .logo { text-align: center; margin-bottom: 18px; }
  .logo img { max-width: 220px; width: 100%; height: auto; }
  h1 { font-size: 18px; margin-bottom: 6px; text-align: center; font-weight: 700; }
  .course { text-align: center; font-size: 14px; color: #65676b; margin-bottom: 18px; }
  .course strong { color: #1877f2; }
  label { display: block; font-weight: 600; font-size: 13px; margin: 10px 0 4px; }
  input { width: 100%; padding: 10px 12px; border: 1px solid #dadde1; border-radius: 8px; font-size: 15px; font-family: inherit; }
  input:focus { outline: none; border-color: #1877f2; }
  .btn { width: 100%; margin-top: 18px; padding: 12px; background: #1877f2; color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; }
  .btn:hover { background: #166fe5; }
  .err { background: #fee2e2; color: #b91c1c; padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 10px; }
  .foot { margin-top: 16px; text-align: center; font-size: 12px; color: #65676b; }
  .foot a { color: #1877f2; }
</style>
</head>
<body>
<form class="box" method="POST" action="{{ url('/slophub/invite/'.$course->invite_token) }}">
  @csrf
  <div class="logo"><img src="{{ url('/img/slophub-logo.png') }}" alt="SlopHub"></div>
  <h1>Opret din konto</h1>
  <div class="course">Du er inviteret til kurset<br><strong>{{ $course->name }}</strong></div>
  @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
  <label>Navn</label>
  <input type="text" name="name" value="{{ old('name') }}" required autofocus>
  <label>Email</label>
  <input type="email" name="email" value="{{ old('email') }}" required>
  <label>Kodeord (min. 6 tegn)</label>
  <input type="password" name="password" required minlength="6">
  <label>Gentag kodeord</label>
  <input type="password" name="password_confirmation" required minlength="6">
  <button class="btn">Opret konto og log ind</button>
  <div class="foot">Har du allerede en konto? <a href="{{ url('/slophub/login') }}">Log ind her</a></div>
</form>
</body>
</html>
