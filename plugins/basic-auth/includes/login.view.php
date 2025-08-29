<form action="" method="POST" style="max-width: 500px; margin: auto; padding-top: 20px">
  <?= csrf() ?>
  <input type="text" value="<?= old_value('email', 'defaultemail@gmail.com') ?>" name="email"><br>
  <input type="text" value="<?= old_value('password') ?>" name="password"><br>
  <br>
  <select name="gender">
    <option value="" <?= old_selected('gender', '') ?>>--select--</option>
    <option value="male" <?= old_selected('gender', 'male') ?>>male</option>
    <option value="female" <?= old_selected('gender', 'female') ?>>female</option>
  </select>
  <br><br>
  <input type="checkbox" <?= old_checked('hello', 'yes') ?> name="hello" value="yes">
  <input type="checkbox" <?= old_checked('bye', 'no') ?> name="bye" value="no">
  <br><br>
  <button>Submit</button>
  <br><br>
</form>