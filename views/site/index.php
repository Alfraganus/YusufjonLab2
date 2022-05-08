<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4">Welcome to Yusufjon's labatory project!</h1>

        <p class="lead">Project's purpose is to make secure authentication with double step varification!</p>

        <p><a class="btn btn-lg btn-success" href="<?=\yii\helpers\Url::to(['login'])?>">Go to authentification page</a></p>
    </div>


</div>
