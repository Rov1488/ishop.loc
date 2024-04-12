//CKEDITOR.replace('editor1');
$('#editor1').ckeditor();

$('.delete').click(function () {
    var res = confirm('Подтвердите действие');
    if(!res) return false;
});

//подключения стили при выборе категории в сайдбаре
$('.sidebar-menu a').each(function () {
    var location = window.location.protocol + '//' + window.location.host + window.location.pathname;
    var link = this.href;
    if(link == location){
        $(this).parent().addClass('active');
        $(this).closest('.treeview').addClass('active');
    }
});

//Сброс выброных атриьутов в фильтре
$('#reset-filter').click(function(){
    $('#filter input[type=radio]').prop('checked', false);
    return false;
});

//Выбор связанных товаров с помощью видета select2
$('.select2').select2({
    placeholder: "Начните вводить наименование товара",
    //minimumInputLength: 2,
    cache: true,
    ajax: {
        url: adminpath + "/product/related-product",
        delay: 250,
        dataType: 'json',
        data: function(params){
            return {
                q: params.term,
                page: params.page
            };
        },
        processResults: function (data, params){
            return {
              results: data.items
            };
        }
    }

});

//Удаления изображение из галлерей
$('.del-item').on('click', function(){
    var res = confirm('Подтвердите действие');
    if(!res) return false;
    var $this = $(this),
        id = $this.data('id'),
        src = $this.data('src');
    $.ajax({
        url: adminpath + '/product/delete-gallery',
        data: {id: id, src: src},
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
            }, 1000);
        },
        error: function(){
            setTimeout(function(){
                $this.closest('.file-upload').find('.overlay').css({'display':'none'});
                alert('Ошибка');
            }, 1000);
        }
    });
});

//Загрузка изображение
if($('div').is('#single')){
    var buttonSingle = $("#single"),
        buttonMulti = $("#multi"),
        file;
}
if(buttonSingle){
    new AjaxUpload(buttonSingle, {
        action: adminpath + buttonSingle.data('url') + "?upload=1",
        data: {name: buttonSingle.data('name')},
        name: buttonSingle.data('name'),
        onSubmit: function(file, ext){
            if (! (ext && /^(jpg|png|jpeg|gif)$/i.test(ext))){
                alert('Ошибка! Разрешены только картинки');
                return false;
            }
            buttonSingle.closest('.file-upload').find('.overlay').css({'display':'block'});

        },
        onComplete: function(file, response){
            setTimeout(function(){
                buttonSingle.closest('.file-upload').find('.overlay').css({'display':'none'});

                response = JSON.parse(response);
                $('.' + buttonSingle.data('name')).html('<img src="/images/' + response.file + '" style="max-height: 150px;">');
            }, 1000);
        }
    });
}
if(buttonMulti){
    new AjaxUpload(buttonMulti, {
        action: adminpath + buttonMulti.data('url') + "?upload=1",
        data: {name: buttonMulti.data('name')},
        name: buttonMulti.data('name'),
        onSubmit: function(file, ext){
            if (! (ext && /^(jpg|png|jpeg|gif)$/i.test(ext))){
                alert('Ошибка! Разрешены только картинки');
                return false;
            }
            buttonMulti.closest('.file-upload').find('.overlay').css({'display':'block'});

        },
        onComplete: function(file, response){
            setTimeout(function(){
                buttonMulti.closest('.file-upload').find('.overlay').css({'display':'none'});

                response = JSON.parse(response);
                $('.' + buttonMulti.data('name')).append('<img src="/images/' + response.file + '" style="max-height: 150px;">');
            }, 1000);
        }
    });
}

//Проверка выбранли категории или селектов
$('#add').on('submit', function () {
    if(!isNumeric($('#category_id').val())){
        alert('Выберите категорию');
        return false;
    }
});
function isNumeric(n) {
    return !isNaN(parseFloat(n) && isFinite(n));
}

//Модификаторы товаров начало

var moddelete = $('#modButton').fadeOut();

function addField() {
    var telnum = parseInt($('#add_mod_area').find('div.addMod:last').attr('id').slice(6))+1;
    var $this = $('#add_mod_area').append('<div id="addmod'+telnum+'" class="addMod panel box box-primary"><div class="box-header with-border"><h4 class="box-title">' +
        '<a data-toggle="collapse" data-parent="#add_mod_area" href="#collapse'+telnum+'" aria-expanded="false" class="collapsed">Модификатор товара №'+telnum+'</a></h4>' +
        '<button id="modButton" type="button" onclick="deleteFiled('+telnum+')" class="btn btn-danger btn-sm pull-right" title="Удалить Модификатор товара"><i class="fa fa-trash"></i></button></div>' +
        '<div id="collapse'+telnum+'" class="form-group panel-collapse collapse">' +
        '<label for="mod-title">Наименование модификатора</label>' +
        '<p><input type="text" name="mod[title]" class="form-control" id="mod-title" data-name="mod-title" placeholder="Наименование модификатора" value=""></p>' +
        '<label for="mod-price">Цена</label>' +
        '<p><input type="text" name="mod[price]" class="form-control" id="mod-price" data-name="mod-price" placeholder="Цена" pattern="^[0-9.]{1,}$" value=""></p></div></div>');


    for (var i = 0; i < telnum; i++){
        if(telnum > 1){
            $this.find('div #addmod'+i).attr(moddelete).fadeIn();

        }else {
            $this.find('div #addmod'+i).attr(moddelete).fadeOut();
        }
    }



}

function deleteFiled(id) {
   var title = $('div #addmod'+id).find('#mod-title').val(),
       price = $('div #addmod'+id).find('#mod-price').val(),
       jsondata = [title, price];
   if($('div #addmod'+id+'#collapse'+id+' input[type=text]').val() == null){
       $('div #addmod'+id).remove();
   }else{
        for(var i = 0; i < jsondata; i++){
            jsondata[i];

        $.ajax({
            url: adminpath + '/product/delete-modification',
            data: {jsondata: jsondata[i]},
            type: 'POST',
            success: function(id){
                    $('div #addmod'+id).remove();
            },
            error: function(){
                    alert('Ошибка');

                }
            });
        }
    }
}






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
//Модификаторы товаров конец

