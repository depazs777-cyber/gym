<h2>Register Client</h2>
<div class="card" style="max-width: 600px;">
    <form action="<?= url("/gym/clients/store") ?>" method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="identification">Identification (ID/Passport)</label>
            <input type="text" id="identification" name="identification" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email">
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone">
        </div>

        <button type="submit" class="btn">Register Client</button>
        <a href="<?= url("/gym/clients") ?>" class="btn" style="background-color: #6c757d;">Cancel</a>
    </form>
</div>
