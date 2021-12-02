<?php
    function saveChatMsg($text){
        $text = htmlents($text);
        $uid = $_SESSION['driverid'];
        $squery = "INSERT INTO chatbox (uid,text) VALUES ($uid,'$text');";
        dbInsert($squery);
    }

    function getChatMsgs($deleted = false, $first = 0, $cant = 20, $reorder = true){
        $messages = array();
        $squery = "";
        if ($reorder){ 
            $squery = "SELECT * FROM (";
        }
        $squery .="SELECT "
            ."chatbox.id AS id, "
            ."chatbox.uid AS uid, "
            ."chatbox.text AS text, "
            ."chatbox.date AS date, "
            ."chatbox.deleted AS deleted, "
            ."drivers.displayname AS name, "
            ."drivers.fullname AS fullname, "
            ."drivers.rank AS rank "
            ."FROM chatbox "
            ."INNER JOIN drivers ON drivers.id = chatbox.uid ";                        
            if (!$deleted) {
                $squery .="WHERE drivers.rank >0 AND chatbox.deleted = 0 ";
            }
            if ($reorder){
               $squery .="ORDER BY chatbox.date DESC LIMIT $first,$cant) AS tmp ORDER BY date;";
            } else {
                $squery .="ORDER BY chatbox.date LIMIT $first,$cant;";
            }

        $result = dbSelect($squery);

        while ($row = mysqli_fetch_assoc($result)){
            $row['text'] = $row['text'];
            if (canEdit($row['uid'],$row['rank'])){
                $row['deleteable'] = 1;
            } else {
                $row['deleteable'] = 0;
            }
           /* if (IS_IN_LOCAL_SERVER) {
                $messages[] = array_map('utf8_encode',$row);
            } else {*/
                $messages[] = array_map('utf8_encode', $row);
           // }
        }
        return $messages;
    }

    function countChatMsgs($onlyOn = true){
        $squery = "SELECT COUNT(*) FROM chatbox ";
        if ($onlyOn) {
            $squery .="WHERE deleted = 0;";
        }
        $result = dbSelect($squery);
        if ($row = mysqli_fetch_array($result)){
            return $row[0];
        }
        return 0;
    }

    function deleteChatMsg($id){
        $squery = "UPDATE chatbox SET deleted=1 WHERE id=$id LIMIT 1";
        return dbUpdate($squery);
    }

    function getChatMsg($id){
        $squery = "SELECT * FROM chatbox WHERE id=$id";
        $result = dbSelect($squery);
        $msg = mysqli_fetch_assoc($result);
        return $msg;
    }

    function flushDeletedChat(){
        if (!isDeveloper()) return false;
        $squery = "DELETE FROM chatbox WHERE deleted = 1";
        $status  = dbDelete($squery);
        return $status;
    }

    function flushChat(){
        if (!isDeveloper()) return false;
        $squery = "DELETE FROM chatbox";
        $status  = dbDelete($squery);
        return $status;
    }
  ?>