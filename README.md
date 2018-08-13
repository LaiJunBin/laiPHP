# Framework
## 基本檔案目錄
```
   |
   |___<app>
   |     |___ <controller>
   |     |___ <views>
   |
   |___<autoload>
   |
   |___<public>
```
---
## 說明
app：存放Model，如app/Example.php

app/controller：存放控制器，處理邏輯

app/views：存放畫面，目前還沒有模板引擎

autoload：自動載入的類別，目前只有處理DB與Route的類別

public：對外公開的檔案，可以在外部直接輸入網址讀取

controller.php 控制器

route.php 處理路由，在autoload/web.php使用

function.php 常用方法寫在這個檔案中，整個程式都可以使用

---

## 部分檔案說明

> function.php

Function Name           | Description
--------------|------
dd(var1,var2...)          | var_dump變數的簡寫
keys(array)               | 回傳陣列的keys
values(array)             | 回傳陣列的values
containsKey(array,key)    | 判斷陣列中是否存在索引為key
contains(array,data)      | 判斷陣列中是否存在資料為data
clearEmpty(array)         | 清除陣列中所有空的元素
code(code)                | 設定http_response_code
redirect(url)             | 轉址
view(file,params=[])      | 顯示views中的文件，也可以傳入參數
get_mime_type($filename)  | 取得檔案的mime type

> DB 使用

Method           | Description
--------------|------
Example::create([key=>value,...])  | 對Example表新增資料
Example::update([key=>value,...],[condition=>value,...])  | 對Example表修改資料
Example::delete([condition=>value,...])  | 對Example表刪除資料
Example::find([condition=>value,...])  | 對Example表尋找資料(一筆)
Example::findall([condition=>value,...])  | 對Example表尋找資料(全部)
Example::contains([condition=>value,...])  | 對Example表尋找資料(回傳是否存在)

> env.php

Const           | Description
--------------|------
HOST | 伺服器位置，如localhost
DATA_BASE | 資料庫名稱
USER_NAME | 資料庫帳號
PASS_WORD | 資料庫密碼

