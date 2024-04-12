<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Новая валюта
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?=ADMIN;?>"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li> <a href="<?=ADMIN;?>/currency"> Список валют</a></li>
        <li class="active"> Новая валюта</li>
    </ol>
</section>

<!-- Main content -->
<section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <form action="<?=ADMIN?>/currency/add" method="post" data-toggle="validator">
                    <div class="box-body">
                        <div class="form-group has-feedback">
                            <label for="title">Наименование валюты</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Наименование валюты" required>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group has-feedback">
                            <label for="code">Код валюты</label>
                            <input type="text" name="code" id="code" class="form-control" placeholder="Наименование валюты" required>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group">
                            <label for="symbol_left">Символ с лева</label>
                            <input type="text" name="symbol_left" id="symbol_left" class="form-control" placeholder="Символ с лева">
                        </div>
                        <div class="form-group">
                            <label for="symbol_right">Символ с права</label>
                            <input type="text" name="symbol_right" id="symbol_right" class="form-control" placeholder="Символ с права">
                        </div>
                        <div class="form-group has-feedback">
                            <label for="value">Значение</label>
                            <input type="text" name="value" id="value" class="form-control" placeholder="Значение" required data-error="Допускается цифры и десятычная точка" pattern="^[0-9.]{1,}$">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            <div class="help-block with-errors"></div>
                        </div>
                        <div class="form-group has-feedback">
                            <label for="base">
                            <input type="checkbox" name="base">
                            Значение</label>

                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-success">Добавить</button>
                    </div>
                </form>


            </div>

        </div>
    </div>

</section>
<!-- /.content -->