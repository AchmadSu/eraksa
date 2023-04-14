<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">    
    <title>Reset Password</title>
</head>
<style>
    :root {
    --blue: #007bff;
    --indigo: #6610f2;
    --purple: #6f42c1;
    --pink: #e83e8c;
    --red: #dc3545;
    --orange: #fd7e14;
    --yellow: #ffc107;
    --green: #28a745;
    --teal: #20c997;
    --cyan: #17a2b8;
    --white: #fff;
    --gray: #6c757d;
    --gray-dark: #343a40;
    --primary: #007bff;
    --secondary: #6c757d;
    --success: #28a745;
    --info: #17a2b8;
    --warning: #ffc107;
    --danger: #dc3545;
    --light: #f8f9fa;
    --dark: #343a40;
    --breakpoint-xs: 0;
    --breakpoint-sm: 576px;
    --breakpoint-md: 768px;
    --breakpoint-lg: 992px;
    --breakpoint-xl: 1200px;
    --font-family-sans-serif: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    --font-family-monospace: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }

    *,
    *::before,
    *::after {
    box-sizing: border-box;
    }

    html {
    font-family: sans-serif;
    line-height: 1.15;
    -webkit-text-size-adjust: 100%;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
    }

    article, aside, figcaption, figure, footer, header, hgroup, main, nav, section {
    display: block;
    }

    body {
    margin: 2%  ;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    text-align: left;
    background-color: #fff;
    }

    [tabindex="-1"]:focus:not(:focus-visible) {
    outline: 0 !important;
    }

    .container,
    .container-fluid,
    .container-sm,
    .container-md,
    .container-lg,
    .container-xl {
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
    margin-right: auto;
    margin-left: auto;
    }

    @media (min-width: 576px) {
    .container, .container-sm {
        max-width: 540px;
    }
    }

    @media (min-width: 768px) {
    .container, .container-sm, .container-md {
        max-width: 720px;
    }
    }

    @media (min-width: 992px) {
    .container, .container-sm, .container-md, .container-lg {
        max-width: 960px;
    }
    }

    @media (min-width: 1200px) {
    .container, .container-sm, .container-md, .container-lg, .container-xl {
        max-width: 1140px;
    }
    }

    hr {
    box-sizing: content-box;
    height: 0;
    overflow: visible;
    }

    h1, h2, h3, h4, h5, h6 {
    margin-top: 0;
    margin-bottom: 0.5rem;
    }

    p {
    margin-top: 0;
    margin-bottom: 1rem;
    }

    button {
    border-radius: 0;
    }

    button:focus:not(:focus-visible) {
    outline: 0;
    }

    input,
    button,
    select,
    optgroup,
    textarea {
    margin: 0;
    font-family: inherit;
    font-size: inherit;
    line-height: inherit;
    }

    button,
    input {
    overflow: visible;
    }

    button,
    select {
    text-transform: none;
    }

    [role="button"] {
    cursor: pointer;
    }

    select {
    word-wrap: normal;
    }

    button,
    [type="button"],
    [type="reset"],
    [type="submit"] {
    -webkit-appearance: button;
    }

    button:not(:disabled),
    [type="button"]:not(:disabled),
    [type="reset"]:not(:disabled),
    [type="submit"]:not(:disabled) {
    cursor: pointer;
    }

    button::-moz-focus-inner,
    [type="button"]::-moz-focus-inner,
    [type="reset"]::-moz-focus-inner,
    [type="submit"]::-moz-focus-inner {
    padding: 0;
    border-style: none;
    }

    .btn {
    display: inline-block;
    font-weight: 400;
    color: #212529;
    text-align: center;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    @media (prefers-reduced-motion: reduce) {
    .btn {
        transition: none;
    }
    }

    .btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
    }

    .btn-primary:hover {
    color: #fff;
    background-color: #0069d9;
    border-color: #0062cc;
    }

    .btn-primary:focus, .btn-primary.focus {
    color: #fff;
    background-color: #0069d9;
    border-color: #0062cc;
    box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.5);
    }

    .btn-primary.disabled, .btn-primary:disabled {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
    }

    .btn-primary:not(:disabled):not(.disabled):active, .btn-primary:not(:disabled):not(.disabled).active,
    .show > .btn-primary.dropdown-toggle {
    color: #fff;
    background-color: #0062cc;
    border-color: #005cbf;
    }

    .btn-primary:not(:disabled):not(.disabled):active:focus, .btn-primary:not(:disabled):not(.disabled).active:focus,
    .show > .btn-primary.dropdown-toggle:focus {
    box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.5);
    }
</style>
<body>
    <div style="margin: 20px, 0" class="container">
        <h3 class="text-center">{{getenv("APP_NAME")}}</h3>
        <h4 class="text-center">{{ $mailData['name'] }}</h4>
        <div class="text-justify">
            <p>
                Halo! <br> Seseorang berusaha untuk mengatur ulang kata sandi akun Eraksa Assets Management System anda.
                Apakah itu anda? <b>Jika YA.</b> Silakan klik tombol berikut untuk pengaturan kata sandi anda. Tombol link berikut ini hanya <b>berlaku dalam waktu 10 menit</b> saja. 
            </p>
            <a href="{{ $mailData['link'] }}">
                <button class="btn btn-primary my-2">Link Reset Password</button>
            </a>
            </p>
            <p><b>Jika BUKAN anda</b>, abaikan saja isi pesan ini. <br> Terima kasih, semoga hari anda menyenangkan!</p>
        </div>
        <div class="footer py-4 bg-light text-center">
            Eraksa - <?php echo date("Y"); ?>
        </div>
    </div>
</body>
</html>