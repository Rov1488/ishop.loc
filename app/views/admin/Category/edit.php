<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Редактирования категории <?=$category->title;?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?=ADMIN;?>"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li> <a href="<?=ADMIN;?>/category"> Список категорий</a></li>
        <li class="active"> Редактирования категории <?=h($category->title);?></li>
    </ol>
</section>

<!-- Main content -->
<section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <form action="<?=ADMIN?>/category/edit" method="post" data-toggle="validator">
                    <div class="box-body">
                        <div class="form-group has-feedback">
                            <label for="title">Наименование категории</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Наименование категории" value="<?=h($category->title);?>" required>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="form-group">
                            <label for="parent_id">Родительская категория</label>
                            <?php  new app\widgets\menu\Menu([
                                'tpl' => WWW . '/menu/select.php',
                                'container' => 'select',
                                'cache' => 0,
                                'cachekey' => 'admin_select',
                                'class' => 'form-control',
                                'attrs' => [
                                    'name' => 'parent_id',
                                    'id' => 'parent_id',
                                ],
                                'prepend' => '<option value="0">Самостоятелная категория</option>',
                            ]);?>
                        </div>
                        <div class="form-group">
                            <label for="keywords">Ключивые слова</label>
                            <input type="text" name="keywords" id="keywords" class="form-control" placeholder="Ключивые слова" value="<?=h($category->keywords);?>">

                        </div>
                        <div class="form-group">
                            <label for="description">Описание</label>
                            <input type="text" name="description" id="description" class="form-control" placeholder="Описание" value="<?=h($category->description);?>">

                        </div>
                    </div>
                    <div class="box-footer">
                        <input type="hidden" name="id" value="<?=$category->id;?>">
                        <button type="submit" class="btn btn-success">Сохранить</button>
                    </div>
                </form>


            </div>

        </div>
    </div>

</section>
<!-- /.content -->