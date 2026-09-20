<div class="auth-card-inner">
    <h1 class="auth-title">Create account</h1>
    <p class="auth-sub">Patient self-registration</p>

    <form method="post" action="<?= url('/register') ?>" autocomplete="off">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="<?= old('name') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone number <span class="optional">(optional)</span></label>
            <input type="tel" id="phone" name="phone" value="<?= old('phone') ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
        </div>

        <button type="submit" class="btn btn-primary btn-block">Create account</button>
    </form>

    <p class="auth-links">
        Already have an account? <a href="<?= url('/login') ?>">Sign in</a>
    </p>
</div>