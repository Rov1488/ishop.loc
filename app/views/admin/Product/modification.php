<div id="add_mod_area file-upload">
    <div class="form-group">
        <label for="mod-title">Наименование модификатора</label>
        <p><input type="text" name="title[]" class="form-control" id="mod-title" data-name="mod-title" placeholder="Наименование модификатора" value="<?php //isset($_SESSION['mod']['title']) ? h($_SESSION['mod']['title']) : null; ?>"></p>
    </div>
    <div class="form-group">
        <label for="mod-price">Цена</label>
        <p><input type="text" name="price[]" class="form-control" id="mod-price" data-name="mod-price" placeholder="Цена" pattern="^[0-9.]{1,}$" value="<?php //isset($_SESSION['mod']['price']) ? h($_SESSION['mod']['price']) : null; ?>"></p>
    </div>
    <div class="box-footer">
        <input type="hidden" name="id" data-id="mod-id" value="<?php //isset($_SESSION['mod']['id']) ? h($_SESSION['mod']['id']) : null; ?>">
        <div class="btn btn-primary pull-left" onclick="addField();">Добавить</div>
    </div>
    <div class="overlay">
        <i class="fa fa-refresh fa-spin"></i>
    </div>
</div>



<div class="table-responsive" id="mod-div">
    <table class="table table-bordered table-hover" name="mod-table[]" id="mod-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Наименование модификатора</th>
            <th>Цена</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td><span data-id="id" class="glyphicon glyphicon-remove text-danger del-mod" aria-hidden="true"></span></td>
        </tr>
        </tbody>
    </table>
    <div class="overlay">
        <i class="fa fa-refresh fa-spin"></i>
    </div>
</div>

<?php /*

 public function modificationProductAction(){
        $ress = json_decode($_POST['jsondata'], true);
        $modification = \R::getAssoc('SELECT id, title, price FROM modification WHERE title = ?', [$ress]);
        $i = 0;
        if (!empty($ress)){
            foreach ($ress as $items){
                    $ress[$i]['title'] = $items['title'];
                    $ress[$i]['price'] = $items['price'];
                $i++;
            }
        }
        debug($ress);

    }


 function addField() {
    var $this = $('#add_mod_area input[type=text]'),
        title = $this.data('mod-title'),
        price = $this.data('mod-price'),
        modArray = [title, price],
        jsondata = JSON.stringify(modArray);
    $.ajax({
        url: adminpath + '/modification-product',
        data: {jsondata: jsondata},
        type: 'POST',
        beforeSend: function(data){
            $this.closest('.file-upload').find('.overlay').css({'display':'block'});
        },
        success: function (data) {

            showMod(data);

            setTimeout(function(){
                $this.closest('.file-upload').find('.overlay').css({'display':'none'});
                if(res == 1){
                    $this.fadeOut();
                }
            }, 1000);

        },
        error: function (xhr, str) {

            setTimeout(function(){
                $this.closest('.file-upload').find('.overlay').css({'display':'none'});
                alert('Возникла ошибка: ' + xhr.responseCode);
            }, 1000);
        }

    });
}


var modtable = $('#mod-div').fadeIn();
function showMod (mod) {
    if(mod){
        modtable.fadeIn();
    }else{
        modtable.fadeOut();
    }
}


$('.del-mod').on('click', function(){
    var res = confirm('Подтвердите действие');
    if(!res) return false;
    var $this = $(this),
        id = $this.data('id');

    $.ajax({
        url: adminpath + '/product/delete-modification',
        data: {id: id},
        type: 'POST',
        beforeSend: function(){
            $this.closest('.file-upload').find('.overlay').css({'display':'block'});
        },
        success: function(res){
            setTimeout(function(){
                $this.closest('.file-upload').find('.overlay').css({'display':'none'});
                if(res == 1){
                    $this.fadeOut();
                }
            }, 500);
        },
        error: function(){
            setTimeout(function(){
                $this.closest('.file-upload').find('.overlay').css({'display':'none'});
                alert('Ошибка');
            }, 500);
        }
    });
});

 */
/*
* e-osgo.uz

function getDriverForm(existingNumberOfDrivers) {
    $.ajax({
        url: "/ajax/get-driver-form",
        data: {
            "existingNumberOfDrivers": existingNumberOfDrivers
        },
        type: "GET",
        timeout: 5000,
        beforeSend: function (xhr) {
            $('.loader').fadeIn();
        },
        success: function (data) {
            $('.loader').fadeOut();
            $('#allowedPeople').append(data);
            if(existingNumberOfDrivers === 4){
                $('#addDriverButton').addClass('disabled');
            }
        },
        complete: function(){
            let hasDatepicker = $(document).find('[data-krajee-kvdatepicker]');
            if (hasDatepicker.length > 0) {
                hasDatepicker.each(function () {
                    $(this).parent().find('.krajee-datepicker').kvDatepicker(eval($(this).attr('data-krajee-kvdatepicker')));
                });
            }
        },
        error: function (e) {
            $('.loader').fadeOut();
            addAlertBox('danger', 'Произошла ошибка при получении данных. Проверьте данные!');
        }
    });
}

function removeDriver(block){
    block.parent().parent().remove();
    $('#addDriverButton').removeClass('disabled');
}

 */



/*Пример для отправки данных виде ассоциативный массива
*

 massgp[i] = {
 mfond: sfond,
 mitsgp: sitsgp,
 mdate: sdate,
 mtypekredit: stypekredit,
 mstatyagp: sstatyagp,
 mcomm: scomm,
 msumm: ssumm,
 mpercent: spercent,
 mcontr: scontr,
 mschet: sschet
 };

 Итоговый рабочий код:
     var jsondata = JSON.stringify(massgp);
         $.ajax({
             type: 'POST',
             url: '/ajaxpf/saveplan.php',
             data: {
             jsondata: jsondata
             },
          success: function(data) {
             alert('Отправили, получили ответ');
             alert(data);
             },
          error:  function(xhr, str){
             alert('Возникла ошибка: ' + xhr.responseCode);
             }
         });

 На сервере смотрю структуру:
 PHP

 $ress = json_decode($_POST['jsondata'], true);

 echo var_dump($ress);
*/


?>
    <?php
    $n = 0;
    $id = isset($_SESSION['mod']['mod_id']) ? h($_SESSION['mod']['mod_id']) : null;
    $title = isset($_SESSION['mod']['title']) ? h($_SESSION['mod']['title']) : null;
    $price = isset($_SESSION['mod']['price']) ? h($_SESSION['mod']['price']) : null;
    $mods = [
        ['mod_id' => $id,
            'title' => $title,
            'price' => $price,],
    ];
    ?>
    <?php
    foreach ($mods as $id => $item):
    $n++;
    ?>
    <div id="add_mod_area" class="file-upload">
        <?php if ($n == 1): ?>
            <div id="addmod<?=$n;?>" class="addMod panel box box-primary">
                <div class="box-header with-border">
                    <h4 class="box-title">
                        <a data-toggle="collapse" data-parent="#add_mod_area" href="#collapse<?=$n;?>" aria-expanded="false" class="collapsed">Модификатор товара №<?=$n;?></a>
                    </h4>
                    <button id="modButton" type="button" onclick="deleteFiled(<?=$n;?>);" class="btn btn-danger btn-sm pull-right" title="Удалить Модификатор товара"><i class="fa fa-trash"></i></button>
                </div>

                <div id="collapse<?=$n;?>" class="form-group panel-collapse collapse">
                    <label for="mod-title">Наименование модификатора</label>
                    <p><input type="text" name="mod-title[<?=$n;?>]" class="form-control" id="mod-title" data-name="mod-title" placeholder="Наименование модификатора" value="<?=$item['title'];?>"></p>
                    <label for="mod-price">Цена</label>
                    <p><input type="text" name="mod-price[<?=$n;?>]" class="form-control" id="mod-price" data-name="mod-price" placeholder="Цена" pattern="^[0-9.]{1,}$" value="<?=$item['price'];?>"></p>
                    <input type="hidden" name="id" id="values" data-id="mod-id" value="<?=$id;?>">
                </div>
            </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <div class="overlay">
            <i class="fa fa-refresh fa-spin"></i>
        </div>
    </div>
<div class="box-footer">
    <div class="btn btn-primary pull-right" onclick="addField();">Добавить модификатора <i class="fa fa-plus"></i></div>
</div>