var base_url = document.mybaseurl;
var distance_data;
var charts_array=[];
Chart.defaults.global = {
    // Boolean - Whether to animate the chart
    animation: true,
    // Number - Number of animation steps
    animationSteps: 40,
    // String - Animation easing effect
    // Possible effects are:
    // [easeInOutQuart, linear, easeOutBounce, easeInBack, easeInOutQuad,
    //  easeOutQuart, easeOutQuad, easeInOutBounce, easeOutSine, easeInOutCubic,
    //  easeInExpo, easeInOutBack, easeInCirc, easeInOutElastic, easeOutBack,
    //  easeInQuad, easeInOutExpo, easeInQuart, easeOutQuint, easeInOutCirc,
    //  easeInSine, easeOutExpo, easeOutCirc, easeOutCubic, easeInQuint,
    //  easeInElastic, easeInOutSine, easeInOutQuint, easeInBounce,
    //  easeOutElastic, easeInCubic]
    animationEasing: "easeInExpo",
    // Boolean - If we should show the scale at all
    showScale: true,
    // Boolean - If we want to override with a hard coded scale
    scaleOverride: false,
    // ** Required if scaleOverride is true **
    // Number - The number of steps in a hard coded scale
    scaleSteps: null,
    // Number - The value jump in the hard coded scale
    scaleStepWidth: null,
    // Number - The scale starting value
    scaleStartValue: null,
    // String - Colour of the scale line
    scaleLineColor: "rgba(80,80,80,.5)",
    // Number - Pixel width of the scale line
    scaleLineWidth: 1,
    // Boolean - Whether to show labels on the scale
    scaleShowLabels: true,
    // Interpolated JS string - can access value
    scaleLabel: "<%if (value>1000){%><%=value/1000%> K<%} else {%><%=value%><%}%>",
    // Boolean - Whether the scale should stick to integers, not floats even if drawing space is there
    scaleIntegersOnly: true,
    // Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
    scaleBeginAtZero: false,
    // String - Scale label font declaration for the scale label
    scaleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
    // Number - Scale label font size in pixels
    scaleFontSize: 11,
    // String - Scale label font weight style
    scaleFontStyle: "normal",
    // String - Scale label font colour
    scaleFontColor: "rgb(220,100,10)",
    // Boolean - whether or not the chart should be responsive and resize when the browser does.
    responsive: false,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: true,
    // Boolean - Determines whether to draw tooltips on the canvas or not
    showTooltips: false,
    // Function - Determines whether to execute the customTooltips function instead of drawing the built in tooltips (See [Advanced - External Tooltips](#advanced-usage-custom-tooltips))
    customTooltips: false,
    // Array - Array of string names to attach tooltip events
    tooltipEvents: ["mousemove", "touchstart", "touchmove"],
    // String - Tooltip background colour
    tooltipFillColor: "rgba(0,0,0,0.8)",
    // String - Tooltip label font declaration for the scale label
    tooltipFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
    // Number - Tooltip label font size in pixels
    tooltipFontSize: 14,
    // String - Tooltip font weight style
    tooltipFontStyle: "normal",
    // String - Tooltip label font colour
    tooltipFontColor: "#fff",
    // String - Tooltip title font declaration for the scale label
    tooltipTitleFontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
    // Number - Tooltip title font size in pixels
    tooltipTitleFontSize: 14,
    // String - Tooltip title font weight style
    tooltipTitleFontStyle: "bold",
    // String - Tooltip title font colour
    tooltipTitleFontColor: "#fff",
    // Number - pixel width of padding around tooltip text
    tooltipYPadding: 6,
    // Number - pixel width of padding around tooltip text
    tooltipXPadding: 6,
    // Number - Size of the caret on the tooltip
    tooltipCaretSize: 8,
    // Number - Pixel radius of the tooltip border
    tooltipCornerRadius: 6,
    // Number - Pixel offset from point x to tooltip edge
    tooltipXOffset: 10,
    // String - Template string for single tooltips
    tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",
    // String - Template string for multiple tooltips
    multiTooltipTemplate: "<%= value %>",
    // Function - Will fire on animation progression.
    onAnimationProgress: function(){},
    // Function - Will fire on animation completion.
    onAnimationComplete: function(){}
};

Number.prototype.format = function(n, x) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
    return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
};

function getRankData(tableInfo, callback){
    var returnData = null;
    $.getJSON(base_url+"/ranking/get/"+tableInfo.field+"/"+tableInfo.year+"/"+tableInfo.month+"/"+tableInfo.game,function(d){
        callback(tableInfo,d);
    });
    return returnData;
}

function setTableData(tableInfo, data){    
    table = $('#data-table-'+tableInfo.field);
    tableBody = $('#data-table-'+tableInfo.field+' tbody');
    tableBody.remove();    
    $.each(data, function (idx, row){
        numPos = idx+1;
        tbRow = $("<tr/>").appendTo(table);
        tbPosCell = $("<td/>",{ text: "#"+numPos}).appendTo(tbRow);
        tbNameCell = $("<td/>").appendTo(tbRow);
        tbNameLink = $("<a/>",{
            class: "table-a",
            href : base_url+"/trips/list/"+row.uid,
            text : row.name
        }).appendTo(tbNameCell);
        row.value /= tableInfo.divider;
        //row.value.format(2,2);
        tbDataCell = $("<td/>",{
            text : tableInfo.prepend+row.value.format(tableInfo.precision,3)+tableInfo.append
        }).appendTo(tbRow);
    });  
    setChartData(tableInfo,data);
}   

function setChartData(tableInfo, data){
    var ctx = $("#data-chart-"+tableInfo.field)[0].getContext("2d");
    var arr_labels = [];
    var arr_data = [];
    var options = {
        maintainAspectRatio: false,
        responsive: true
    };    
    if (typeof charts_array[tableInfo.field] != 'undefined') {
        charts_array[tableInfo.field].clear();
        charts_array[tableInfo.field].destroy();
    }
   $.each(data, function (idx, row){
        arr_labels.push(row.name);
        arr_data.push(row.value);
    });
    var charData = {
        labels: arr_labels,
        datasets: [
            {
                fillColor : "rgb(220,100,10)",
                strokeColor : "rgba(80,80,80,.5)",
                highlightFill: "rgba(220,220,220,0.75)",
                highlightStroke: "rgba(220,220,220,1)",
                data: arr_data
            }
        ]
    };
    charts_array[tableInfo.field] = new Chart(ctx).Bar(charData, options);
}

function updateRankTables (){    
    $('#loading-btn').show();
   monthIval = $("#filterByMonth").val();
   yearIval = $("#filterByYear").val();
   gameIval = $("#filterByGame").val();
   //console.debug(monthIval);
   tableInfo = {
        field: 'driven',
        year : yearIval,        
        month: monthIval,
        game: gameIval,
        prepend : '',
        append : ' Km',
        precision: 1,
        divider : 1,
    };
   
    getRankData(tableInfo,setTableData);

    tableInfo = {
        field: 'income',
        year : yearIval,        
        month: monthIval,
        game: gameIval,    
        prepend : '$ ',
        append : '',
        precision: 1,
        divider : 1
    };
    getRankData(tableInfo,setTableData);
    tableInfo = {
        field : 'trips',
        year : yearIval,        
        month: monthIval,
        game: gameIval,
        prepend : '',
        append : '',
        precision: 0,
        divider : 1
    };
    getRankData(tableInfo,setTableData);       
     tableInfo = {
        field : 'mass',
        year : yearIval,        
        month: monthIval,
        game: gameIval,
        prepend : '',
        append : ' Ton',
        precision: 1,
        divider : 1000
    };
    getRankData(tableInfo,setTableData);       
    $('#loading-btn').hide();
}

jQuery(function(){
  updateRankTables('month');
});

