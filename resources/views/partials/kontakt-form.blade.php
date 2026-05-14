<form method="POST" action="/kontakt" class="kontakt-form">
    <input type="hidden" name="ts" value="" data-kontakt-ts>
    <input type="hidden" name="kilde" value="{{ request()->path() }}">
    <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;">
        <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <label>Navn<input type="text" name="name" required></label>
    <label>E-mail<input type="email" name="email" required></label>
    <label>Besked<textarea name="msg" required rows="5"></textarea></label>
    <button type="submit">Send besked</button>
</form>

<style>
    .kontakt-form {
        display: grid; gap: 14px;
        max-width: 560px;
        margin: 24px 0;
    }
    .kontakt-form label {
        display: grid; gap: 6px;
        font-size: 0.95rem; color: #1a2733; font-weight: 500;
    }
    .kontakt-form input[type=text],
    .kontakt-form input[type=email],
    .kontakt-form textarea {
        padding: 10px 12px;
        border: 1px solid #cdd5e0; border-radius: 6px;
        font: inherit; color: #1a2733;
        background: #fff;
    }
    .kontakt-form textarea { resize: vertical; min-height: 120px; }
    .kontakt-form input:focus, .kontakt-form textarea:focus {
        outline: none; border-color: #3498db; box-shadow: 0 0 0 3px #eaf4fb;
    }
    .kontakt-form button {
        justify-self: start;
        padding: 10px 22px;
        background: #3498db; color: #fff;
        border: 0; border-radius: 6px;
        font: inherit; font-weight: 600; cursor: pointer;
    }
    .kontakt-form button:hover { background: #2980b9; }
</style>
<script>
    (function () {
        var el = document.currentScript.previousElementSibling;
        while (el && !el.classList.contains('kontakt-form')) el = el.previousElementSibling;
        if (!el) return;
        var ts = el.querySelector('[data-kontakt-ts]');
        if (ts) ts.value = Math.floor(Date.now() / 1000);
    })();
</script>
