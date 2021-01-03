# PHP Framework

> `舊`使用範例(API)：　[todolist](https://github.com/LaiJunBin/laiPHP_TodoList)

## 基本檔案目錄
```
   |
   |___<app>
   |     |___ <controller>
   |     |___ <middleware>
   |     |___ <views>
   |
   |___<autoload>
   |
   |___<public>
   |
   |___<samplefiles>
```
---
## 說明
app：存放Model，如app/Example.php

app/controller：存放控制器，處理邏輯

app/middleware：存放中介層

app/views：存放畫面，有提供模板引擎(.lai.php)

autoload：自動載入的類別

public：對外公開的檔案，可以在外部直接輸入網址讀取

controller.php 控制器

route.php 處理路由，在autoload/web.php使用

function.php 常用方法寫在這個檔案中，整個程式都可以使用

samplefiles：存放cli相關的檔案(盡量不要動)

---

## 說明

> function.php

Method           | Description
--------------|------
dd(var1,var2...)          | 印出變數並結束程式執行
dump(var1,var2...)        | 印出變數但不結束程式執行
keys(array)               | 回傳陣列的keys
values(array)             | 回傳陣列的values
containsKey(array,key)    | 判斷陣列中是否存在索引為key
contains(array,data)      | 判斷陣列中是否存在資料為data
array_fetch(array, keys)  | 為陣列中所有元素抓取keys的元素，階層以.分隔
array_only(array, keys)   | 為陣列中第一個元素抓取keys的元素，階層以.分隔
array_get(array, key)     | 以key取得陣列中的元素，階層以.分隔
array_copy(a, b, key)     | 複製b陣列中key的資料至a陣列
array_map_recursive(array, func) | 遞迴執行array_map
clearEmpty(array)         | 清除陣列中所有空的元素
Response(res=null)        | 產生Response()物件
get_mime_type(filename)  | 取得檔案的mime type
old(key, default) | 取得舊的輸入資料
method_field(method) | 產生表單欺騙隱藏欄位
str_replace_first(search, replace, subject) | 字串取代(只取代第一筆)
clean_url(url)          | 將url多餘的/清除
public_path(path)       | 取得public path
assets_path(path)       | 取得assets path
route(name, params=[])     | 取得特定名稱路由的url，可帶入參數
include_model(model)           | 引入 Model
include_models(models=[])           | 引入多個Model



> request.php

Request 物件

Method              | Description
--------------|------
status                    | 取得Http Response Code
method                    | 取得 方法 GET POST ....
get(array,data)           | 取得GET資料
post(array)               | 取得POST資料
file(name)                | 取得FILE
json(array,key)           | 取得JSON資料
all(array)                | 依順序回傳第一個非空的資料，順序為JSON,POST,GET
headers(key=null)         | 回傳對應的header，如果key=null將回傳所有header
{route params}            | 回傳對應的路由參數，例如 $request->name

> response.php

Response 物件

Method              | Description
--------------|------
json(json_data=[])        | json response
code(code=200)            | 設定http_response_code
redirect(url)             | 轉址
redirectRoute(name, params=[])  | 轉址到特定route
view(file,params=[])      | 顯示views中的文件，也可以傳入參數
log(status_code=200)      | 在主控台打印Response Log
withInput()               | 可搭配 old() 使用，將使用者輸入的值放回input
withErrors(errors=[])     | 可在模板引擎中取得$errors變數

> collection.php

Collection 物件
Method              | Description
--------------|------
clear()      | 清除資料
set(data)   | 設定data
assign(items) | 覆蓋items
get(index)   | 取得第index筆資料
includes(item) | 是否包含item
first() | 取得第一筆資料
last()  | 取得最後一筆資料
count() | 取得集合中的資料數
map(func)   | map處理
filter(func)      | filter處理
find(func)        | 取得第一筆符合條件的資料
forEach(func)     | foreach處理
fetch(keys)       | 同 array_fetch
only(keys)       | 同 array_only
to_array()    | 取得陣列
recursive(func) | 遞迴集合
sum()             | 加總
flat()            | 攤平一層
join(glup)        | join資料


> auth.php

Auth 物件

Method              | Description
--------------|------
login(user)  | 登入
logout()     | 登出
check()      | 檢查是否登入
user()       | 取得登入的使用者
get_user_class() | 取得user使用的class

> 命令使用

Command                  | Description
--------------|------
php lai serv           | 啟動測試伺服器
php lai make:controller | 產生控制器(controller)
php lai make:middleware | 產生中介層(middleware)
php lai make:model      | 產生模型(model)
php lai route:list      | 顯示當前所有路由
php lai help            | 幫助訊息

> DB 使用

Example為Model Class

DB為基底類別

Method           | Description
--------------|------
DB::execute(SQL) | 執行SQL語法
Example::count()  | 取得Example表的資料筆數
Example::create([key=>value,...])  | 對Example表新增資料
Example::update([key=>value,...],[condition=>value,...])  | 對Example表修改資料
Example::delete([condition=>value,...])  | 對Example表刪除資料
Example::get(index)  | 取得Example表的第i筆資料
Example::get([orderby=>value,...])  | 取得Example表所有資料
Example::get(index, [orderby=>value,...])  | 取得Example表排序後的第i筆資料
Example::find([condition=>value,...])  | 對Example表尋找資料(一筆)
Example::findall([condition=>value,...])  | 對Example表尋找資料(全部)
Example::contains([condition=>value,...])  | 對Example表尋找資料(回傳是否存在)
$example->save()   | 將$example存入資料庫
$example->delete() | 將$example從資料庫刪除
$example->update([key => value, ...]) | 將$example更新
$example->isInstance() | 檢查$example是否為資料庫中的實體


> 關聯模型

Method           | Description
--------------|------
$this->hasOne(模型, 外鍵, 主鍵); | 一對一
$this->hasMany(模型, 外鍵, 主鍵);  | 一對多
$this->through(模型, 外鍵, 主鍵)->hasMany(模型, 外鍵, 主鍵);  | 多對多

參數說明：
* 模型 => app資料夾底下的Model，Ex: Example
* 外鍵 => 若不輸入則預設為 模型_id， Ex: example_id，`若為多對多關聯則預設值為呼叫的模型class_id`
* 主鍵 => 若不輸入則預設為 id


## Template Engine

檔名.php => 檔名.lai.php 即可使用模板引擎的語法，目前提供以下幾種

* 目前foreach迴圈僅能支援一維陣列

假設
* $data = 100
* $items = ['a', 'b', 'c']
* $html = `'<div>hello world</div>'`

<table>
   <thead>
      <tr>
         <th>Syntax</th>
         <th>Result</th>
         <th>Description</th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <td>#include_model('Model')</td>
         <td>
            無
         </td>
         <td>執行#後面的語法，在模板最上面引入Model，也可以用來宣告變數</td>
      </tr>
      <tr>
         <td>{{ $html }}</td>
         <td>
            &lt;div&gt;hello world&lt;/div&gt;
         </td>
         <td>輸出經過處理的 $html 變數</td>
      </tr>
      <tr>
         <td>!{{ $html }}</td>
         <td>hello world</td>
         <td>輸出未經過處理的 $html 變數</td>
      </tr>
      <tr>
      <td>

```
@for ($i = 1; $i <= 3; $i++){
   <div>{{ $i }}</div>
}

```
<br>
      </td>
      <td>

```
<div>1</div>
<div>2</div>
<div>3</div>
```
<br>
      </td>
      <td>for 迴圈</td>
      </tr>
<tr>
<td>

```
@foreach ($items as $item){
   <div>{{ $item }}</div>
}

```
<br>
      </td>
      <td>

```
<div>a</div>
<div>b</div>
<div>c</div>
```
<br>
      </td>
      <td>foreach 迴圈(value)</td>
      </tr>
<tr>
<td>

```
@foreach ($items as $key => $value){
      <div>{{ $key . '=>'. $value }}</div>
}

```
<br>
      </td>
      <td>

```
<div>0 => a</div>
<div>1 => b</div>
<div>2 => c</div>
```
<br>
      </td>
      <td>foreach 迴圈(key-value)</td>
      </tr>
      <tr>
      <td>

```
@if($data == 100){
   <div>Test</div>
}

```
<br>
      </td>
      <td>

```
<div>Test</div>
```
<br>
      </td>
      <td>if 判斷</td>
      </tr>
      <tr>
      <td>

```
@if($count==3 && false){
   <div>Count = 3</div>
}else{
   <div>Hello!!!</div>
}

```
<br>
      </td>
      <td>

```
<div>Hello!!!</div>
```
<br>
      </td>
      <td>if-else 判斷</td>
      </tr>
      <tr>
      <td>

```
@extends(layout)
```
<br>
      </td>
      <td>

```
layout.lai.php 檔案中的內容
```
<br>
      </td>
      <td>模板繼承</td>
      </tr>
      <tr>
      <td>

```
@yield(title, 'welcome')
@yield(main)
```
<br>
      </td>
      <td>

```
title預設值為welcome，可以被section填入
設定main的欄位給section填入
```
<br>
      </td>
      <td>設定欄位</td>
      </tr>
      <tr>
      <td>

```
@section(title, 'use section set title')
@section(main){
   <div>main</div>
}
```
<br>
      </td>
      <td>

```
將yield的title設定為 use section set title
將yield(main)取代為<div>main</div>
```
<br>
      </td>
      <td>填入yield</td>
      </tr>
   </tbody>
</table>


> env.php

Const           | Description
--------------|------
HOST | 伺服器位置，如localhost
PORT | Port
DATA_BASE | 資料庫名稱
USER_NAME | 資料庫帳號
PASS_WORD | 資料庫密碼

## License
[MIT](https://github.com/LaiJunBin/laiphp/blob/master/LICENSE)