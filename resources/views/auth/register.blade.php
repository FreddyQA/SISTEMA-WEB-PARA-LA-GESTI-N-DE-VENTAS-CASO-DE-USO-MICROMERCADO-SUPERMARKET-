<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Registro | SUPERMARKET</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
rel="stylesheet">

<style>

:root{
--bg1:#0f172a;
--bg2:#1e293b;
--bg3:#312e81;
--card:rgba(255,255,255,.15);
--text:#ffffff;
--sub:#cbd5e1;
}

@media (prefers-color-scheme: light){

:root{
--bg1:#dbeafe;
--bg2:#e0e7ff;
--bg3:#f8fafc;
--card:rgba(255,255,255,.75);
--text:#0f172a;
--sub:#475569;
}

}

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{

min-height:100vh;

display:flex;
justify-content:center;
align-items:center;

background:
linear-gradient(
135deg,
var(--bg1),
var(--bg2),
var(--bg3)
);

background-size:400% 400%;

animation:fondo 15s ease infinite;

overflow:hidden;
}

@keyframes fondo{

0%{background-position:0% 50%;}
50%{background-position:100% 50%;}
100%{background-position:0% 50%;}

}

.circle{
position:absolute;
border-radius:50%;
background:rgba(255,255,255,.08);
}

.c1{
width:300px;
height:300px;
top:-80px;
left:-80px;
}

.c2{
width:250px;
height:250px;
right:-80px;
bottom:-80px;
}

.c3{
width:140px;
height:140px;
left:120px;
bottom:100px;
}

.register-card{

width:100%;
max-width:550px;

background:var(--card);

backdrop-filter:blur(20px);

border:1px solid rgba(255,255,255,.2);

border-radius:30px;

padding:40px;

box-shadow:
0 25px 50px rgba(0,0,0,.30);

animation:entrada .8s ease;
}

@keyframes entrada{

from{
opacity:0;
transform:translateY(40px);
}

to{
opacity:1;
transform:translateY(0);
}

}

.logo{

width:95px;
height:95px;

margin:auto;

border-radius:24px;

display:flex;
justify-content:center;
align-items:center;

font-size:40px;

color:white;

background:
linear-gradient(
135deg,
#6366f1,
#7c3aed
);

box-shadow:
0 15px 35px rgba(99,102,241,.4);
}

h2{
color:var(--text);
font-weight:800;
}

.subtitle{
color:var(--sub);
font-size:14px;
}

.form-label{
color:var(--text);
font-weight:600;
}

.input-group-text{
background:rgba(255,255,255,.10);
border:none;
color:var(--text);
}

.form-control{

height:50px;

border:none;

background:rgba(255,255,255,.10);

color:var(--text);
}

.form-control:focus{

background:rgba(255,255,255,.18);

box-shadow:
0 0 0 3px rgba(99,102,241,.25);

color:var(--text);
}

.form-control::placeholder{
color:#94a3b8;
}

.btn-register{

height:55px;

border:none;

font-weight:700;

border-radius:14px;

background:
linear-gradient(
135deg,
#6366f1,
#7c3aed
);

transition:.3s;
}

.btn-register:hover{

transform:translateY(-2px);

box-shadow:
0 15px 30px rgba(99,102,241,.35);

}

.footer{
text-align:center;
margin-top:20px;
font-size:12px;
color:var(--sub);
}

.spinner-border-sm{
display:none;
}

</style>

</head>

<body>

<div class="circle c1"></div>
<div class="circle c2"></div>
<div class="circle c3"></div>

<div class="register-card">

<div class="text-center mb-4">

<div class="logo">
<i class="fas fa-user-plus"></i>
</div>

<h2 class="mt-3">
Crear Cuenta
</h2>

<p class="subtitle">
Registro de usuarios del sistema SUPERMARKET
</p>

</div>

<form method="POST"
      action="{{ route('register') }}"
      id="registerForm">

@csrf

<div class="mb-3">

<label class="form-label">
Nombre Completo
</label>

<div class="input-group">

<span class="input-group-text">
<i class="fas fa-user"></i>
</span>

<input
type="text"
name="name"
value="{{ old('name') }}"
class="form-control @error('name') is-invalid @enderror"
placeholder="Ingrese su nombre"
required>

</div>

@error('name')
<div class="text-danger small mt-1">
{{ $message }}
</div>
@enderror

</div>

<div class="mb-3">

<label class="form-label">
Correo Electrónico
</label>

<div class="input-group">

<span class="input-group-text">
<i class="fas fa-envelope"></i>
</span>

<input
type="email"
name="email"
value="{{ old('email') }}"
class="form-control @error('email') is-invalid @enderror"
placeholder="correo@ejemplo.com"
required>

</div>

@error('email')
<div class="text-danger small mt-1">
{{ $message }}
</div>
@enderror

</div>

<div class="mb-3">

<label class="form-label">
Contraseña
</label>

<div class="input-group">

<span class="input-group-text">
<i class="fas fa-lock"></i>
</span>

<input
id="password"
type="password"
name="password"
class="form-control"
placeholder="Contraseña"
required>

<span
class="input-group-text"
onclick="togglePassword()"
style="cursor:pointer">

<i class="fas fa-eye"></i>

</span>

</div>

</div>

<div class="mb-4">

<label class="form-label">
Confirmar Contraseña
</label>

<div class="input-group">

<span class="input-group-text">
<i class="fas fa-shield-halved"></i>
</span>

<input
type="password"
name="password_confirmation"
class="form-control"
placeholder="Repita la contraseña"
required>

</div>

</div>

<button
type="submit"
class="btn btn-register text-white w-100"
id="btnRegister">

<span id="btnText">

<i class="fas fa-user-plus me-2"></i>
Registrar Usuario

</span>

<span
class="spinner-border spinner-border-sm"
id="loader"></span>

</button>

</form>

<div class="footer">

© {{ date('Y') }} SUPERMARKET

<br>

Sistema de Gestión Empresarial · Supermarket

</div>

</div>

<script>

function togglePassword(){

const input =
document.getElementById('password');

input.type =
input.type === 'password'
? 'text'
: 'password';

}

document
.getElementById('registerForm')
.addEventListener('submit', function(){

document
.getElementById('btnText')
.style.display='none';

document
.getElementById('loader')
.style.display='inline-block';

});

</script>

</body>
</html>