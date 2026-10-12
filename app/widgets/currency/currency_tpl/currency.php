<option value="" class="label"><?=h($this->currency['code']);?></option>
<?php foreach ($this->currencies as $k => $v):  ?>

<?php if($k != $this->currency['code']): ?>

<option value="<?=h($k);?>"><?=h($k);?></option>

<?php endif; ?>
<?php endforeach; ?>