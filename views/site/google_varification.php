<div class="container">
    <h1 class="text-center">Please, scan the QR code in your google autenticator app and paste the result here</h1>
    <br>
    <?php use yii\bootstrap4\ActiveForm;
    $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <img style="width: 100%" src="<?=$googleAuth->getURL('YusufjonsLab', 'Project Lab', \app\models\GoogleAuthenticator::SECRET) ?>" alt="">
        </div>
    </div>
    <div class="col-md-10">
        <div class="form-group">
           <?= $form->field($model, 'code')->textInput(['autofocus' => true,'placeholder'=>'Please paste the digits']) ?>
        </div>
    </div>

</div>

    <div class="align-self-center text-center">
        <button type="submit" class="btn btn-primary mb-2">Confirm </button>
    </div>
    <?php ActiveForm::end(); ?>
</div>
