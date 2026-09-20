<div class="auth-card-inner">
    <h1 class="auth-title">Sign in</h1>
    <p class="auth-sub">Access the <?= e(APP_NAME) ?></p>

    <form method="post" action="<?= url('/login') ?>" autocomplete="off">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>"
                   required autofocus placeholder="you@hospital.com">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary btn-block">Sign in</button>
    </form>

    <p class="auth-links">
        New patient? <a href="<?= url('/register') ?>">Create an account</a>
    </p>
</div>