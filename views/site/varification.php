<div class="container">
    <h1 class="text-center">4 digit code has been sent to test.yusufjon@gmail.com, please paste the digits here, session
        is active for
        3 minutes</h1>
    <br>
    <p>
        <strong>email:</strong>test.yusufjon@gmail.com <br>
        <strong>password:</strong> 123456yusu
        <br><strong>the sent code to email:</strong>  <?=$sentCode?><br>

    </p>
    <br>
    <?php use yii\bootstrap4\ActiveForm;

    $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <?= $form->field($model, 'code')->textInput(['autofocus' => true, 'placeholder' => 'Please paste the digits']) ?>
            </div>
        </div>
    </div>

    <div class="align-self-center text-center">
        <button type="submit" class="btn btn-primary mb-2">Confirm</button>
    </div>
    <?php ActiveForm::end(); ?>
</div>
