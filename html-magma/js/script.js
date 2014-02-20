$(function(){
    
    $(".slider").easySlider({
        auto:           true,
        continuous:     true,
        numericAndBtn:  true,
        numericId:      "sliderControls",
        prevText:       "",
        nextText:       "",
        speed: 			800,
        pause:			2000
    });
    
    /**
     * lets check whether or not a user is using a mobile device
     */
    var bIsMobile;
    
    var regex01 = /android|(bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|ad|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i;
    
    var regex02 = /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i;
    
    var sNavigator = navigator.userAgent||navigator.vendor||window.opera;
    
    if (regex01.test(sNavigator) || regex02.test(sNavigator.substr(0,4))) {
        bIsMobile = true;
    } else {
        bIsMobile = false;
    }
    
    
    /**
     * this function performs scroll to the top of the document
     * 
     * @param {String} sScrollArea scroll area element
     * @param {String} sScrollBtn scroll area element
     * @returns {Boolean}
     */
    function scroll_to_top(sScrollArea, sScrollBtn) {
        // don't perform scroll if we have a mobile device
        if (bIsMobile) {
            return false;
        }
        
        var nOffset = $(this).scrollTop();
        var bIsShown = false;
        
        function hover_fx() {
            if (bIsShown) {
                //hovering effects
                $(sScrollArea).animate({opacity: 0.3}, 1000);
                
                $(sScrollArea).hover(function() {
                    $(this).stop(true, true).animate({opacity: 1}, 500);
                }, function(){
                    $(this).stop(true, true).animate({opacity: 0.3}, 500);
                });
            }
        }
        
        function animate() {
            if (nOffset >= 500 && bIsShown === false) {
                //show "scroll to the top" area
                bIsShown = true;
                $(sScrollArea + ", " + sScrollBtn).stop(true, true).fadeIn(1000);
                setTimeout(hover_fx, 2000);
            } else if (nOffset <= 500 && bIsShown === true) {
                bIsShown = false;
                $(sScrollArea + ", " + sScrollBtn).stop(true, true).fadeOut(1000);
            }
        }
        
        hover_fx();
        animate();
        
        $(window).scroll(function() {
            nOffset = $(this).scrollTop();
            animate();
        });
        
        $(sScrollArea + ", " + sScrollBtn).click(function(e) {
            e.preventDefault();
            $("html, body").animate({scrollTop: 0}, 1000);
        });
         
    }
    
    scroll_to_top(".scrollTopArea", ".scrollTopBtn");
    
    // code monkey's on the run...)))
    
    // this function puts images into canvas
    function runFilter(oCanv, filter, oImg) {
        var c = oCanv;
        var idata = Filters.filterImage(filter, oImg);
        c.width = idata.width;
        c.height = idata.height;
        var ctx = c.getContext('2d');
        ctx.putImageData(idata, 0, 0);
    }
    
    // cloning images
    var oImgs = document.getElementsByClassName("portItemImg");
    var oCanvs = document.getElementsByClassName("desaturatedImg");
    
    $(".portItemImg").each(function(i) {
        var oImg = $(this);
        oImg.load(function() {
            runFilter(oCanvs[i], Filters.grayscale, oImgs[i]);
        });
    });

    
    
    // desaturator
    
	$(".portfolioMenu a").click(function(event) {
        event.preventDefault();
        var obj = $(this);
        var sID = obj.attr("data-toggle-id");
        var bHasItems = false;
        var bOneGroup = false;
        
        // do we have any selected items?
        function has_selected_items() {
            var regex = /selected_gr/i;
            
            $(".portfolioItem").each(function() {
                if (regex.test($(this).attr("class"))) {
                    bHasItems = true;
                }
            });
            //console.log("are there selected elems: "+bHasItems);
            return bHasItems;
        }
        
        // this function can count groups of selected elements, if any. 
        // returns true, if there is only one group
        function count_sel_groups() {
            var obj = $(".selectedP");
            //console.log("groups selected: "+obj.length);
            if (obj.length > 1) { 
                bOneGroup = false;
            } else {
                bOneGroup = true;
            }
        }
        
        // we can saturate necessary elements...
        function disengage_btn() {
            if (!bHasItems) {
                //no elements has been selected, so show 'em all
                $(".portfolioItem").each(function() {
                    $(this).css({opacity: 1}).find(".desaturatedImg").css({"z-index": -1}); 
                    
                });
                
            } else if (bOneGroup) {
                //what if we have only one group of elements...
                $(".portfolioItem").each(function() {
                    var obj = $(this);
                    if (obj.attr("class").indexOf(sID) > -1) {
                        obj.removeClass("selected_" + sID); 
                    }
                    obj.css({opacity: 1}).find(".desaturatedImg").css({"z-index": -1})
                });
                
            } else {
                $(".portfolioItem").each(function() {
                    var obj = $(this);
                    if (obj.attr("class").indexOf(sID) > -1) {
                        obj.css({opacity: 0.5}).find(".desaturatedImg").css({"z-index": 1});
                        obj.removeClass("selected_" + sID);
                    }
                });
            }
        }
        
        
        // ...or desaturate them
        function engage_btn() {
            if (!bHasItems) {
                //no elements has been selected
                $(".portfolioItem").each(function() {
                    var obj = $(this);
                    if (obj.attr("class").indexOf(sID) == -1) {
                        obj.css({opacity: 0.5}).find(".desaturatedImg").css({"z-index": 1}); 
                    } else {
                        obj.addClass("selected_" + sID);
                    }
                });
            } else {
                $(".portfolioItem").each(function() {
                    var obj = $(this);
                    if (obj.attr("class").indexOf(sID) > -1) {
                        obj.css({opacity: 1}).find(".desaturatedImg").css({"z-index": -1});
                        obj.addClass("selected_" + sID);
                    }
                });
            }
        }
        
        
        
        //console.log(sID + ", " + obj.hasClass("selectedP"));
        
        if ((!obj.hasClass("selectedP"))) {
            
            obj.addClass("selectedP");
            count_sel_groups();
            has_selected_items();
            engage_btn();
            
        } else {
            
            count_sel_groups();
            obj.removeClass("selectedP");
            has_selected_items();
            disengage_btn();
            
        }
    });
    
});

