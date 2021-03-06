<?php
/**Класс создания вида
 * 
 * 
 */
class View
{
    /**Функция анализа текущих GET параметров 
     * Используется для формирования правильной
     * ссылки при работе с пагинацией и формами
     * $without -- указание на ключ элемента ГЕТ массива
     * который не нужно включать в результирующую строку
     * return String
     */
    function currentGetParameters($without=" "){
        $getParamString = "";
        foreach ($_GET as $key => $value){
            if ($key == $without){
                continue;
            }else{
                $getParamString = $getParamString."&".$key."=".$value;
            }
        }
        return $getParamString;
    }
    //шапка таблицы
    public $tableHead = '
    <table class="table table-hover">
        <thead>
            <tr>
            <th scope="col">Номер задачи</th>
            <th scope="col">Пользователь</th>
            <th scope="col">e-mail</th>
            <th scope="col">Текст задачи</th>
            <th scope="col">Статус</th>
            </tr>
        </thead>
        <tbody>
    ';
    //низ таблицы 
    public $tableBottom='
    </tbody>
    </table>
    ';

    /**функция создания вида задач для пользователей
     * возвращает таблицу HTML
     * заполненную данными из БД
     * 
     * return String
     * 
     */
    function createViewForUser($objectPDO){
        $table=$this->tableHead;
        foreach ($objectPDO as $row){
            if ($row['id_status'] == 1){
                $status="В работе";
            }else{
                $status="Выполнено";
            }
            $table=$table.
            '<tr>
            <th scope="row">'.$row['id'].'</th>
            <td>'.$row['username'].'</td>
            <td>'.$row['email'].'</td>
            <td>'.$row['text_task'].'</td>
            <td>'.$status.'</td>
          </tr>';
        }
        $table=$table.$this->tableBottom;
        return $table;
    }

        /**функция создания вида задач для администратора
     * возвращает таблицу HTML
     * заполненную данными из БД
     * и ссылкой на отображение формы для редактирования задачи
     * return String
     * 
     */
    function createViewForAdmin($objectPDO){
        $table=$this->tableHead;
        foreach ($objectPDO as $row){
            if ($row['id_status'] == 1){
                $status="В работе";
            }else{
                $status="Выполнено";
            }
            $table=$table.
            '<tr>
            <th scope="row">'.$row['id'].'</th>
            <td>'.$row['username'].'</td>
            <td>'.$row['email'].'</td>
            <td>'.$row['text_task'].'</td>
            <td>'.$status.'</td>
            <td><a href="index.php?editTask='.$row['id'].$this->currentGetParameters("editTask").'">РЕДАКТИРОВАТЬ<a></td>
          </tr>';
        }
        $table=$table.$this->tableBottom;
        return $table;
    }

    /**функция создания формы для фильтрации данных
     * принимает объект модели
     * возвращает форму для выбора гет параметров для фильтрации данных
     *  
     * return String
     * 
     */
    function createFiltartionForm($Model){
        $allUsers = $Model->getFromDatabase($Model->allUsersQuery);
        $allStatuses = $Model->getFromDatabase($Model->allStatusQuery);
        $html='<form action="index.php" method="GET">';
        if (isset($_GET["menu"])){//добавляем Гет параметр в случае работы в панели администратора ($_GET["menu"] == admin)
            $html=$html.'<input type="hidden" name="menu" value="'.$_GET["menu"].'">';
        }
        $html=$html.$this->tableHead;
        $html=$html.'<tr><th scope="row"></th><td><select name="filteruser">
        <option value=""></option>';
        foreach ($allUsers as $user){
            $html=$html.'<option value='.$user['id'].'>'.$user['username'].'</option>';
        }
        $html=$html.'</select></td><td></td><td></td><td>
                    <select name="filterstatus">
                    <option value=""></option>';
        foreach ($allStatuses as $status){
            $html=$html.'<option value='.$status['id'].'>'.$status['status_name'].'</option>';
        }
        $html = $html.'</select></tr>'.$this->tableBottom.'<input type="submit" value="ФИЛЬТРОВАТЬ ДАННЫЕ"></form>';
        return $html;
    }
    

    /**функция создания кнопок пагинации
     * 
     * return String
     * 
     */
    function createPagination($rowsTable){
        $html="<ul class='pagination'>";
        $html=$html."<li><a href='index.php?page=1".$this->currentGetParameters("page")."'>Начало</a></li>";
        if (isset($_GET["page"])){
            $currentPage=$_GET["page"];
        }else{
            $currentPage=1;
        }
        $counter=1;
        do {
            $html=$html."<li><a href='index.php?page=".$currentPage.$this->currentGetParameters("page")."'>".$currentPage."</a></li>";
            $currentPage++;
            $counter++;
        } while (($currentPage > ceil($rowsTable/3)) xor ($counter < 11));
        $html=$html."</ul>";
        return $html;
    }
    /**функция создания окошка редактирования задачи
     * input $array (строка задачи из БД)
     * return String
     * 
     */
    function createEditTaskForm($array){
        $currentGetArray=$this->currentGetParameters();
        $currentGetArray[0]="?";
        if ($array[0]["id_status"] == 1){
            $html='<form action="index.php'.$currentGetArray.'" method="POST">
                <p>
                    <input type="radio" id="contactChoice1" name="idStatus" value="1" checked> 
                    <label for="contactChoice1">ЗАДАЧА В РАБОТЕ</label>
                    <input type="radio" id="contactChoice2" name="idStatus" value="2">
                    <label for="contactChoice2">ЗАДАЧА ВЫПОЛНЕНА</label>
                </p>
                <p>
                    <label for="textTask">Текст задачи</label>
                    <textarea type="text" name="textTask" required>'.$array[0]["text_task"].'</textarea>
                </p>
                <p>
                    <input type="submit" value="Редактировать задачу">
                </p>
        
               </form>';
        }else if($array[0]["id_status"] == 2){
            $html='<form action="index.php'.$currentGetArray.'" method="POST">
            <p>
                <input type="radio" id="contactChoice1" name="idStatus" value="1"> 
                <label for="contactChoice1">ЗАДАЧА В РАБОТЕ</label>
                <input type="radio" id="contactChoice2" name="idStatus" value="2" checked>
                <label for="contactChoice2">ЗАДАЧА ВЫПОЛНЕНА</label>
            </p>
            <p>
                <label for="textTask">Текст задачи</label>
                <textarea type="text" name="textTask" required>'.$array[0]["text_task"].'</textarea>
            </p>
            <p>
                <input type="submit" value="Редактировать задачу">
            </p>
    
           </form>';    
        }
        return $html;
    }
 }
?>
