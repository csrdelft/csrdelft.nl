mobl.provides('CivitatsMobiel');
mobl.provides('mobl.ui');
persistence.debug = false;CivitatsMobiel.ingelogd = mobl.ref(false);
CivitatsMobiel.loginNaam = mobl.ref("");
CivitatsMobiel.ww = mobl.ref("");
CivitatsMobiel.LoginType = mobl.defineType('CivitatsMobiel.LoginType', null, {loginState: null});

CivitatsMobiel.Maaltijd = mobl.defineType('CivitatsMobiel.Maaltijd', null, {aantal: null,datum: null,gesloten: null,id: null,max: null,status: null,tekst: null});

CivitatsMobiel.loginMapper = function(json) {
   var __this = this;
  return json.login;
};

CivitatsMobiel.maaltijdMapper = function(json) {
   var __this = this;
  return json.maaltijden;
};

CivitatsMobiel.tekstKnopMaaltijd = function(maaltijd) {
   var __this = this;
  if(maaltijd.status == "AF" || maaltijd.status == "") {
    if(maaltijd.max - maaltijd.aantal <= 0) {
      return "VOL";
    } else {
      return "aanmelden";
    }
  } else {
    return "afmelden";
  }
};

CivitatsMobiel.aanAfmelden = function(maaltijd, callback) {
  var __this = this;
  var result__ = maaltijd.gesloten == "0";
  if(result__) {
    var result__ = maaltijd.status == "AF" || maaltijd.status == "";
    if(result__) {
      var result__ = maaltijd.max - maaltijd.aantal <= 0;
      if(result__) {
        var result__ = "Maaltijd vol";
        if(callback && callback.apply) callback(result__);
        return;
        if(callback && callback.apply) callback(); return;
      } else {
        {
          CivitatsMobiel.Maal.aanmelden(maaltijd.id, function(result__) {
            var tmp1461 = result__;
            var result__ = tmp1461;
            var a = result__;
            var result__ = a == 1 || a == "true";
            if(result__) {
              var result__ = "AAN";
              maaltijd.status = result__;
              var result__ = "aanmelden succesvol";
              if(callback && callback.apply) callback(result__);
              return;
              if(callback && callback.apply) callback(); return;
            } else {
              {
                var result__ = "aanmelden niet gelukt";
                if(callback && callback.apply) callback(result__);
                return;
                if(callback && callback.apply) callback(); return;
              }
            }
          });
        }
      }
    } else {
      {
        CivitatsMobiel.Maal.afmelden(maaltijd.id, function(result__) {
          var tmp1462 = result__;
          var result__ = tmp1462;
          var a = result__;
          var result__ = a == 1 || a == "true";
          if(result__) {
            var result__ = "AF";
            maaltijd.status = result__;
            var result__ = "afmelden succesvol";
            if(callback && callback.apply) callback(result__);
            return;
            if(callback && callback.apply) callback(); return;
          } else {
            {
              var result__ = "afmelden niet gelukt";
              if(callback && callback.apply) callback(result__);
              return;
              if(callback && callback.apply) callback(); return;
            }
          }
        });
      }
    }
  } else {
    {
      if(callback && callback.apply) callback(); return;
    }
  }
};


CivitatsMobiel.Login = {
  login: function(callback) {
    var url = "" + (this.root ? this.root : "") + "/tools/ajax/ajax.php";
    $.ajax({
       url: url,
       dataType: "json",
       type: "GET",
       
       error: function(_, message, error) {
         console.error(message);
         console.error(error);
         callback(null);
       },
       success: function(data) {
          var result = CivitatsMobiel.loginMapper(data, callback);
          if(result !== undefined) {
            callback(result);
          }
       }
    });
  }
  ,
  inloggen: function(naam, ww, callback) {
    var url = "" + (this.root ? this.root : "") + "/tools/ajax/login.php?user=" + naam + "&pass=" + ww + "&url=/civitasmobiel/www/civitasmobiel.html";
    $.ajax({
       url: url,
       dataType: "json",
       type: "GET",
       
       error: function(_, message, error) {
         console.error(message);
         console.error(error);
         callback(null);
       },
       success: function(data) {
          var result = mobl.dummyMapper(data, callback);
          if(result !== undefined) {
            callback(result);
          }
       }
    });
  }
  
};

CivitatsMobiel.Maal = {
  next10: function(callback) {
    var url = "" + (this.root ? this.root : "") + "/tools/ajax/ajax.php?next10maal=1";
    $.ajax({
       url: url,
       dataType: "json",
       type: "GET",
       
       error: function(_, message, error) {
         console.error(message);
         console.error(error);
         callback(null);
       },
       success: function(data) {
          var result = CivitatsMobiel.maaltijdMapper(data, callback);
          if(result !== undefined) {
            callback(result);
          }
       }
    });
  }
  ,
  afmelden: function(maaltijd, callback) {
    var url = "" + (this.root ? this.root : "") + "/tools/ajax/ajax.php?afmelden=" + maaltijd;
    $.ajax({
       url: url,
       dataType: "json",
       type: "GET",
       
       error: function(_, message, error) {
         console.error(message);
         console.error(error);
         callback(null);
       },
       success: function(data) {
          var result = mobl.dummyMapper(data, callback);
          if(result !== undefined) {
            callback(result);
          }
       }
    });
  }
  ,
  aanmelden: function(maaltijd, callback) {
    var url = "" + (this.root ? this.root : "") + "/tools/ajax/ajax.php?aanmelden=" + maaltijd;
    $.ajax({
       url: url,
       dataType: "json",
       type: "GET",
       
       error: function(_, message, error) {
         console.error(message);
         console.error(error);
         callback(null);
       },
       success: function(data) {
          var result = mobl.dummyMapper(data, callback);
          if(result !== undefined) {
            callback(result);
          }
       }
    });
  }
  
};

CivitatsMobiel.root = function(callback, screenCallback) {
  var root621 = $("<div>");
  var subs__ = new mobl.CompSubscription();
  
  var ajaxlogin = mobl.ref(null);
  CivitatsMobiel.Login.inloggen(CivitatsMobiel.loginNaam.get(), CivitatsMobiel.ww.get(), function(result__) {
    var tmp1463 = result__;
    var result__ = tmp1463;
    ajaxlogin.set(result__);
    
  });
  
  var tmp1448 = mobl.ref("Loading...");
  
  var nodes528 = $("<span>");
  root621.append(nodes528);
  subs__.addSub((mobl.ui.generic.whenLoaded)(ajaxlogin, mobl.ref(mobl.ui.generic.loadingStyle), tmp1448, function(_, callback) {
    var root622 = $("<span>");
    var subs__ = new mobl.CompSubscription();
    
    var login = mobl.ref(null);
    CivitatsMobiel.Login.login(function(result__) {
      var tmp1464 = result__;
      var result__ = tmp1464;
      login.set(result__);
      
    });
    
    var tmp1447 = mobl.ref("Loading...");
    
    var nodes529 = $("<span>");
    root622.append(nodes529);
    subs__.addSub((mobl.ui.generic.whenLoaded)(login, mobl.ref(mobl.ui.generic.loadingStyle), tmp1447, function(_, callback) {
      var root623 = $("<span>");
      var subs__ = new mobl.CompSubscription();
      
      var tmp1416 = mobl.ref(login.get() == "true");
      subs__.addSub(login.addEventListener('change', function() {
        tmp1416.set(login.get() == "true");
      }));
      
      
      var node116 = $("<span>");
      root623.append(node116);
      var condSubs53 = new mobl.CompSubscription();
      subs__.addSub(condSubs53);
      var oldValue53;
      var renderCond53 = function() {
        var value71 = tmp1416.get();
        if(oldValue53 === value71) return;
        oldValue53 = value71;
        var subs__ = condSubs53;
        subs__.unsubscribe();
        node116.empty();
        if(value71) {
          var nodes530 = $("<span>");
          node116.append(nodes530);
          subs__.addSub((mobl.ui.generic.group)(function(_, callback) {
            var root624 = $("<span>");
            var subs__ = new mobl.CompSubscription();
            
            var tmp1415 = mobl.ref([new mobl.Tuple("Maaltijden", "lib/toolbar/icon_users.png", CivitatsMobiel.maaltijden)]);
            subs__.addSub(mobl.ref(CivitatsMobiel.maaltijden).addEventListener('change', function() {
              tmp1415.set([new mobl.Tuple("Maaltijden", "lib/toolbar/icon_users.png", CivitatsMobiel.maaltijden)]);
            }));
            
            var nodes531 = $("<span>");
            root624.append(nodes531);
            subs__.addSub((mobl.ui.generic.tabSet)(tmp1415, function(_, callback) {
              var root625 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              callback(root625); return subs__;
              return subs__;
            }, function(node) {
              var oldNodes = nodes531;
              nodes531 = node.contents();
              oldNodes.replaceWith(nodes531);
            }));
            callback(root624); return subs__;
            
            return subs__;
          }, function(node) {
            var oldNodes = nodes530;
            nodes530 = node.contents();
            oldNodes.replaceWith(nodes530);
          }));
          
          
        } else {
          
        }
      };
      renderCond53();
      subs__.addSub(tmp1416.addEventListener('change', function() {
        renderCond53();
      }));
      
      
      var tmp1446 = mobl.ref(login.get() == "false");
      subs__.addSub(login.addEventListener('change', function() {
        tmp1446.set(login.get() == "false");
      }));
      
      
      var node117 = $("<span>");
      root623.append(node117);
      var condSubs54 = new mobl.CompSubscription();
      subs__.addSub(condSubs54);
      var oldValue54;
      var renderCond54 = function() {
        var value72 = tmp1446.get();
        if(oldValue54 === value72) return;
        oldValue54 = value72;
        var subs__ = condSubs54;
        subs__.unsubscribe();
        node117.empty();
        if(value72) {
          var nodes532 = $("<span>");
          node117.append(nodes532);
          subs__.addSub((mobl.ui.generic.group)(function(_, callback) {
            var root626 = $("<span>");
            var subs__ = new mobl.CompSubscription();
            
            var tmp1423 = mobl.ref(false);
            
            
            var tmp1422 = mobl.ref(null);
            
            
            var tmp1421 = mobl.ref(null);
            
            var nodes533 = $("<span>");
            root626.append(nodes533);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp1421, tmp1422, tmp1423, function(_, callback) {
              var root627 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var tmp1417 = mobl.ref("U kunt hier inloggen");
              
              
              var tmp1419 = mobl.ref(null);
              
              
              var tmp1418 = mobl.ref(null);
              
              var nodes534 = $("<span>");
              root627.append(nodes534);
              subs__.addSub((mobl.label)(tmp1417, tmp1418, tmp1419, function(_, callback) {
                var root628 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root628); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes534;
                nodes534 = node.contents();
                oldNodes.replaceWith(nodes534);
              }));
              callback(root627); return subs__;
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes533;
              nodes533 = node.contents();
              oldNodes.replaceWith(nodes533);
            }));
            
            var tmp1432 = mobl.ref(false);
            
            
            var tmp1431 = mobl.ref(null);
            
            
            var tmp1429 = mobl.ref(null);
            
            var nodes535 = $("<span>");
            root626.append(nodes535);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp1429, tmp1431, tmp1432, function(_, callback) {
              var root629 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var tmp1424 = mobl.ref("naam");
              
              
              var tmp1428 = mobl.ref(null);
              
              
              var tmp1427 = mobl.ref(null);
              
              
              var tmp1426 = mobl.ref(null);
              
              
              var tmp1425 = mobl.ref(null);
              
              var nodes536 = $("<span>");
              root629.append(nodes536);
              subs__.addSub((mobl.ui.generic.textField)(CivitatsMobiel.loginNaam, tmp1424, tmp1425, tmp1426, mobl.ref(mobl.ui.generic.textFieldStyle), mobl.ref(mobl.ui.generic.textFieldInvalidStyle), tmp1427, tmp1428, function(_, callback) {
                var root630 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root630); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes536;
                nodes536 = node.contents();
                oldNodes.replaceWith(nodes536);
              }));
              callback(root629); return subs__;
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes535;
              nodes535 = node.contents();
              oldNodes.replaceWith(nodes535);
            }));
            
            var tmp1439 = mobl.ref(false);
            
            
            var tmp1438 = mobl.ref(null);
            
            
            var tmp1437 = mobl.ref(null);
            
            var nodes537 = $("<span>");
            root626.append(nodes537);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp1437, tmp1438, tmp1439, function(_, callback) {
              var root631 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var tmp1433 = mobl.ref("wachtwoord");
              
              
              var tmp1436 = mobl.ref(null);
              
              
              var tmp1435 = mobl.ref(null);
              
              
              var tmp1434 = mobl.ref(null);
              
              var nodes538 = $("<span>");
              root631.append(nodes538);
              subs__.addSub((mobl.ui.generic.passwordField)(CivitatsMobiel.ww, tmp1433, tmp1434, mobl.ref(mobl.ui.generic.textFieldStyle), tmp1435, tmp1436, function(_, callback) {
                var root632 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root632); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes538;
                nodes538 = node.contents();
                oldNodes.replaceWith(nodes538);
              }));
              callback(root631); return subs__;
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes537;
              nodes537 = node.contents();
              oldNodes.replaceWith(nodes537);
            }));
            
            var tmp1445 = mobl.ref(false);
            
            
            var tmp1444 = mobl.ref(null);
            
            
            var tmp1443 = mobl.ref(null);
            
            var nodes539 = $("<span>");
            root626.append(nodes539);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp1443, tmp1444, tmp1445, function(_, callback) {
              var root633 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var tmp1442 = mobl.ref(function(event, callback) {
                                   if(event && event.stopPropagation) event.stopPropagation();
                                   mobl.call('CivitatsMobiel.root', [mobl.ref(false), mobl.ref("slide")], function(result__) {
                                   var tmp1465 = result__;
                                   if(callback && callback.apply) callback(); return;
                                   });
                                 });
              
              
              var tmp1441 = mobl.ref("inloggen");
              
              var nodes540 = $("<span>");
              root633.append(nodes540);
              subs__.addSub((mobl.ui.generic.button)(tmp1441, mobl.ref(mobl.ui.generic.buttonStyle), mobl.ref(mobl.ui.generic.buttonPushedStyle), tmp1442, function(_, callback) {
                var root634 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root634); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes540;
                nodes540 = node.contents();
                oldNodes.replaceWith(nodes540);
              }));
              callback(root633); return subs__;
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes539;
              nodes539 = node.contents();
              oldNodes.replaceWith(nodes539);
            }));
            callback(root626); return subs__;
            
            
            
            
            return subs__;
          }, function(node) {
            var oldNodes = nodes532;
            nodes532 = node.contents();
            oldNodes.replaceWith(nodes532);
          }));
          
          
        } else {
          
        }
      };
      renderCond54();
      subs__.addSub(tmp1446.addEventListener('change', function() {
        renderCond54();
      }));
      
      callback(root623); return subs__;
      
      
      return subs__;
    }, function(node) {
      var oldNodes = nodes529;
      nodes529 = node.contents();
      oldNodes.replaceWith(nodes529);
    }));
    callback(root622); return subs__;
    
    
    return subs__;
  }, function(node) {
    var oldNodes = nodes528;
    nodes528 = node.contents();
    oldNodes.replaceWith(nodes528);
  }));
  callback(root621); return subs__;
  
  
  return subs__;
};

CivitatsMobiel.maaltijden = function(elements, callback) {
  var root635 = $("<span>");
  var subs__ = new mobl.CompSubscription();
  
  var tmp1449 = mobl.ref("meld je aan of af voor een maatlijd");
  
  
  var tmp1451 = mobl.ref(null);
  
  var nodes541 = $("<span>");
  root635.append(nodes541);
  subs__.addSub((mobl.ui.generic.header)(tmp1449, tmp1451, function(_, callback) {
    var root636 = $("<span>");
    var subs__ = new mobl.CompSubscription();
    callback(root636); return subs__;
    return subs__;
  }, function(node) {
    var oldNodes = nodes541;
    nodes541 = node.contents();
    oldNodes.replaceWith(nodes541);
  }));
  
  var aj = mobl.ref(null);
  CivitatsMobiel.Maal.next10(function(result__) {
    var tmp1466 = result__;
    var result__ = tmp1466;
    aj.set(result__);
    
  });
  
  var tmp1459 = mobl.ref("Loading...");
  
  var nodes542 = $("<span>");
  root635.append(nodes542);
  subs__.addSub((mobl.ui.generic.whenLoaded)(aj, mobl.ref(mobl.ui.generic.loadingStyle), tmp1459, function(_, callback) {
    var root637 = $("<span>");
    var subs__ = new mobl.CompSubscription();
    
    var node118 = mobl.loadingSpan();
    root637.append(node118);
    var list31;
    var listSubs__ = new mobl.CompSubscription();
    subs__.addSub(listSubs__);
    var renderList31 = function() {
      var subs__ = listSubs__;
      list31 = aj.get();
      list31.list(function(results31) {
        node118.empty();
        for(var i31 = 0; i31 < results31.length; i31++) {
          (function() {
            var iternode31 = $("<span>");
            node118.append(iternode31);
            var maaltijd;
            maaltijd = mobl.ref(mobl.ref(results31), i31);
            
            var tmp1458 = mobl.ref(false);
            
            
            var tmp1457 = mobl.ref(null);
            
            
            var tmp1456 = mobl.ref(null);
            
            var nodes543 = $("<span>");
            iternode31.append(nodes543);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp1456, tmp1457, tmp1458, function(_, callback) {
              var root638 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var date = mobl.ref(mobl.DateTime.fromTimestamp(maaltijd.get().datum * 1000));
              
              var tmp1452 = mobl.ref(date.get().getDate() + "-" + date.get().getMonth() + "-" + date.get().getFullYear() + " " + maaltijd.get().tekst);
              subs__.addSub(date.addEventListener('change', function() {
                tmp1452.set(date.get().getDate() + "-" + date.get().getMonth() + "-" + date.get().getFullYear() + " " + maaltijd.get().tekst);
              }));
              subs__.addSub(mobl.ref(maaltijd, 'tekst').addEventListener('change', function() {
                tmp1452.set(date.get().getDate() + "-" + date.get().getMonth() + "-" + date.get().getFullYear() + " " + maaltijd.get().tekst);
              }));
              
              
              var tmp1454 = mobl.ref(null);
              
              
              var tmp1453 = mobl.ref(null);
              
              var nodes544 = $("<span>");
              root638.append(nodes544);
              subs__.addSub((mobl.label)(tmp1452, tmp1453, tmp1454, function(_, callback) {
                var root639 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root639); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes544;
                nodes544 = node.contents();
                oldNodes.replaceWith(nodes544);
              }));
              
              var a = mobl.ref(CivitatsMobiel.tekstKnopMaaltijd(maaltijd.get()));
              
              var tmp1455 = mobl.ref(function(event, callback) {
                                   if(event && event.stopPropagation) event.stopPropagation();
                                   CivitatsMobiel.aanAfmelden(maaltijd.get(), function(result__) {
                                     var tmp1468 = result__;
                                     var result__ = mobl.alert(tmp1468);
                                     var tmp1467 = result__;
                                     var result__ = CivitatsMobiel.tekstKnopMaaltijd(maaltijd.get());
                                     a.set(result__);
                                     if(callback && callback.apply) callback(); return;
                                   });
                                 });
              
              var nodes545 = $("<span>");
              root638.append(nodes545);
              subs__.addSub((mobl.ui.generic.sideButton)(a, mobl.ref(mobl.ui.generic.sideButtonStyle), mobl.ref(mobl.ui.generic.sideButtonPushedStyle), tmp1455, function(_, callback) {
                var root640 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root640); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes545;
                nodes545 = node.contents();
                oldNodes.replaceWith(nodes545);
              }));
              callback(root638); return subs__;
              
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes543;
              nodes543 = node.contents();
              oldNodes.replaceWith(nodes543);
            }));
            
            var oldNodes = iternode31;
            iternode31 = iternode31.contents();
            oldNodes.replaceWith(iternode31);
            
            
          }());
        }
        mobl.delayedUpdateScrollers();
        subs__.addSub(list31.addEventListener('change', function() { listSubs__.unsubscribe(); renderList31(true); }));
        subs__.addSub(aj.addEventListener('change', function() { listSubs__.unsubscribe(); renderList31(true); }));
      });
    };
    renderList31();
    
    callback(root637); return subs__;
    
    return subs__;
  }, function(node) {
    var oldNodes = nodes542;
    nodes542 = node.contents();
    oldNodes.replaceWith(nodes542);
  }));
  callback(root635); return subs__;
  
  
  
  return subs__;
};
