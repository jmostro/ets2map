function updateRecsTable (){    
    $('#loading-btn').show();
    recsIval = $("#filterByRecruits").val();
    tableInfo = {
        recs: recsIval,
    };
   
    getRecsData(tableInfo,setTableData);      
    $('#loading-btn').hide();
}

function getRecsData(tableInfo, callback){
    var returnData = null;
    $.getJSON(base_url+"/admin/recruits/get/"+tableInfo.recs,function(d){
        callback(tableInfo,d);
    });
    return returnData;
}

function setTableData(tableInfo, data){
    table = $('#user-list');
    tableBody = $('#user-list tbody');
    tableBody.remove();
    $.each(data, function (i, row){
        tbRow = $("<tr/>").appendTo(table);
        tbRealUserName = $("<td/>").appendTo(tbRow);
        tbRealUserNameLink = $("<a/>",{
            class: "table-a",
            href : base_url+"/users/view/info/"+row.uid,
            target: "_blank",
            text : row.realusername
        }).appendTo(tbRealUserName);
        tbFullName = $("<td/>",{ text: row.fullname}).appendTo(tbRow);
        tbSteamId = $("<td/>").appendTo(tbRow);
        tbSteamIdLink = $("<a/>",{
            class: "table-a",
            href : "http://steamcommunity.com/profiles/"+row.steam_id,
            target: "_blank",
            text : row.steam_id
        }).appendTo(tbSteamId); 
        if(row.facebook === '' ){
            tbFacebook = $("<td/>",{ text: "Sin perfil"}).appendTo(tbRow);
        }else{
            tbFacebook = $("<td/>").appendTo(tbRow);
            tbFacebookLink = $("<a/>",{
                class: "table-a",
                href : row.facebook,
                target: "_blank",
                text : "Perfil"
            }).appendTo(tbFacebook); 
        }
        tbAge = $("<td/>",{text : row.age}).appendTo(tbRow);
        tbCountry = $("<td/>",{text : row.country}).appendTo(tbRow);
        tbOtherVTC = $("<td/>",{text : row.othervtc}).appendTo(tbRow);
        tbWhyLatam = $("<td/>",{text : row.whylatam}).appendTo(tbRow);
        tbAccept = $("<td/>").appendTo(tbRow);
        tbAcceptYes = $("<a/>",{
            class: "table-a",
            href : base_url+"/admin/recruits/accept/"+row.uid,
            text : "Sí"
        }).appendTo(tbAccept);
        tbSlash = $("<span/>",{ text: " - "}).appendTo(tbAccept);
        tbAcceptNo = $("<a/>",{
            class: "table-a",
            href : base_url+"/admin/recruits/decline/"+row.uid,
            text : "No"
        }).appendTo(tbAccept);
    });  
}

jQuery(function(){
  updateRecsTable('recs');
});