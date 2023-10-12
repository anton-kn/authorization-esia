<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <title>Вход на сайт</title>
</head>

<body>
    <div class="container">
        <h3>Вход в систему</h3>
        <form method="POST" action="{{ route('store') }}" enctype="multipart/form-data">
            @csrf
            <label for="inputEmail3" class="col-sm-2 col-form-label">Эл. почта</label>
            <div class="col-sm-10">
                <input type="email" name="email" class="form-control" id="inputEmail3"
                    value="an-cniazev2012@yandex.ru">
            </div>
            <label for="inputPassword3" class="col-sm-2 col-form-label">Пароль</label>
            <div class="col-sm-10">
                <input type="password" name="password" class="form-control" id="inputPassword3" value="">
            </div>
            <div class="mb-3">
                <label for="formFile" class="form-label">Суть заявления</label>
                <input class="form-control" type="file" name="file[]" id="formFile">
            </div>
            <div class="mb-3">
                <label for="formFile" class="form-label">Приложения к заявлению</label>
                <input class="form-control" type="file" name="file[]" id="formFile">
            </div>
            <button type="submit" class="btn btn-primary">Подать документы</button>
        </form>
    </div>
</body>

</html>
