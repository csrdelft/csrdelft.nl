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
  if(maaltijd.status == "AF") {
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
  var result__ = maaltijd.status == "AF";
  if(result__) {
    var result__ = maaltijd.max - maaltijd.aantal <= 0;
    if(result__) {
      var result__ = mobl.JSON.parse("{\"\": \"Maaltijd vol\"}");
      if(callback && callback.apply) callback(result__);
      return;
      if(callback && callback.apply) callback(); return;
    } else {
      {
        CivitatsMobiel.Maal.aanmelden(maaltijd.id, function(result__) {
          var tmp216 = result__;
          var result__ = tmp216;
          if(callback && callback.apply) callback(result__);
          return;
          if(callback && callback.apply) callback(); return;
        });
      }
    }
  } else {
    {
      CivitatsMobiel.Maal.afmelden(maaltijd.id, function(result__) {
        var tmp217 = result__;
        var result__ = tmp217;
        if(callback && callback.apply) callback(result__);
        return;
        if(callback && callback.apply) callback(); return;
      });
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
  var root58 = $("<div>");
  var subs__ = new mobl.CompSubscription();
  
  var tmp152 = mobl.ref("C.S.R. Pauper website 2.0");
  
  
  var tmp153 = mobl.ref(null);
  
  var nodes52 = $("<span>");
  root58.append(nodes52);
  subs__.addSub((mobl.ui.generic.header)(tmp152, tmp153, function(_, callback) {
    var root59 = $("<span>");
    var subs__ = new mobl.CompSubscription();
    callback(root59); return subs__;
    return subs__;
  }, function(node) {
    var oldNodes = nodes52;
    nodes52 = node.contents();
    oldNodes.replaceWith(nodes52);
  }));
  
  var ajaxlogin = mobl.ref(null);
  CivitatsMobiel.Login.inloggen(CivitatsMobiel.loginNaam.get(), CivitatsMobiel.ww.get(), function(result__) {
    var tmp218 = result__;
    var result__ = tmp218;
    ajaxlogin.set(result__);
    
  });
  
  var tmp191 = mobl.ref("Loading...");
  
  var nodes53 = $("<span>");
  root58.append(nodes53);
  subs__.addSub((mobl.ui.generic.whenLoaded)(ajaxlogin, mobl.ref(mobl.ui.generic.loadingStyle), tmp191, function(_, callback) {
    var root60 = $("<span>");
    var subs__ = new mobl.CompSubscription();
    
    var login = mobl.ref(null);
    CivitatsMobiel.Login.login(function(result__) {
      var tmp219 = result__;
      var result__ = tmp219;
      login.set(result__);
      
    });
    
    var tmp190 = mobl.ref("Loading...");
    
    var nodes54 = $("<span>");
    root60.append(nodes54);
    subs__.addSub((mobl.ui.generic.whenLoaded)(login, mobl.ref(mobl.ui.generic.loadingStyle), tmp190, function(_, callback) {
      var root61 = $("<span>");
      var subs__ = new mobl.CompSubscription();
      
      var tmp157 = mobl.ref(login.get() == "true");
      subs__.addSub(login.addEventListener('change', function() {
        tmp157.set(login.get() == "true");
      }));
      
      
      var node6 = $("<span>");
      root61.append(node6);
      var condSubs4 = new mobl.CompSubscription();
      subs__.addSub(condSubs4);
      var oldValue4;
      var renderCond4 = function() {
        var value10 = tmp157.get();
        if(oldValue4 === value10) return;
        oldValue4 = value10;
        var subs__ = condSubs4;
        subs__.unsubscribe();
        node6.empty();
        if(value10) {
          var nodes55 = $("<span>");
          node6.append(nodes55);
          subs__.addSub((mobl.ui.generic.group)(function(_, callback) {
            var root62 = $("<span>");
            var subs__ = new mobl.CompSubscription();
            
            var tmp154 = mobl.ref(function(event, callback) {
                                 if(event && event.stopPropagation) event.stopPropagation();
                                 mobl.call('CivitatsMobiel.maaltijden', [mobl.ref(false), mobl.ref("slide")], function(result__) {
                                 var tmp220 = result__;
                                 if(callback && callback.apply) callback(); return;
                                 });
                               });
            
            
            var tmp156 = mobl.ref(false);
            
            
            var tmp155 = mobl.ref(null);
            
            var nodes56 = $("<span>");
            root62.append(nodes56);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp154, tmp155, tmp156, function(_, callback) {
              var root63 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              root63.append("maaltijden");
              callback(root63); return subs__;
              return subs__;
            }, function(node) {
              var oldNodes = nodes56;
              nodes56 = node.contents();
              oldNodes.replaceWith(nodes56);
            }));
            callback(root62); return subs__;
            
            return subs__;
          }, function(node) {
            var oldNodes = nodes55;
            nodes55 = node.contents();
            oldNodes.replaceWith(nodes55);
          }));
          
          
        } else {
          
        }
      };
      renderCond4();
      subs__.addSub(tmp157.addEventListener('change', function() {
        renderCond4();
      }));
      
      
      var tmp189 = mobl.ref(login.get() == "false");
      subs__.addSub(login.addEventListener('change', function() {
        tmp189.set(login.get() == "false");
      }));
      
      
      var node7 = $("<span>");
      root61.append(node7);
      var condSubs5 = new mobl.CompSubscription();
      subs__.addSub(condSubs5);
      var oldValue5;
      var renderCond5 = function() {
        var value11 = tmp189.get();
        if(oldValue5 === value11) return;
        oldValue5 = value11;
        var subs__ = condSubs5;
        subs__.unsubscribe();
        node7.empty();
        if(value11) {
          var nodes57 = $("<span>");
          node7.append(nodes57);
          subs__.addSub((mobl.ui.generic.group)(function(_, callback) {
            var root64 = $("<span>");
            var subs__ = new mobl.CompSubscription();
            
            var tmp163 = mobl.ref(false);
            
            
            var tmp162 = mobl.ref(null);
            
            
            var tmp161 = mobl.ref(null);
            
            var nodes58 = $("<span>");
            root64.append(nodes58);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp161, tmp162, tmp163, function(_, callback) {
              var root65 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var tmp158 = mobl.ref("U kunt hier inloggen");
              
              
              var tmp160 = mobl.ref(null);
              
              
              var tmp159 = mobl.ref(null);
              
              var nodes59 = $("<span>");
              root65.append(nodes59);
              subs__.addSub((mobl.label)(tmp158, tmp159, tmp160, function(_, callback) {
                var root66 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root66); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes59;
                nodes59 = node.contents();
                oldNodes.replaceWith(nodes59);
              }));
              callback(root65); return subs__;
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes58;
              nodes58 = node.contents();
              oldNodes.replaceWith(nodes58);
            }));
            
            var tmp171 = mobl.ref(false);
            
            
            var tmp170 = mobl.ref(null);
            
            
            var tmp169 = mobl.ref(null);
            
            var nodes60 = $("<span>");
            root64.append(nodes60);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp169, tmp170, tmp171, function(_, callback) {
              var root67 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var tmp164 = mobl.ref("naam");
              
              
              var tmp168 = mobl.ref(null);
              
              
              var tmp167 = mobl.ref(null);
              
              
              var tmp166 = mobl.ref(null);
              
              
              var tmp165 = mobl.ref(null);
              
              var nodes61 = $("<span>");
              root67.append(nodes61);
              subs__.addSub((mobl.ui.generic.textField)(CivitatsMobiel.loginNaam, tmp164, tmp165, tmp166, mobl.ref(mobl.ui.generic.textFieldStyle), mobl.ref(mobl.ui.generic.textFieldInvalidStyle), tmp167, tmp168, function(_, callback) {
                var root68 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root68); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes61;
                nodes61 = node.contents();
                oldNodes.replaceWith(nodes61);
              }));
              callback(root67); return subs__;
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes60;
              nodes60 = node.contents();
              oldNodes.replaceWith(nodes60);
            }));
            
            var tmp178 = mobl.ref(false);
            
            
            var tmp177 = mobl.ref(null);
            
            
            var tmp176 = mobl.ref(null);
            
            var nodes62 = $("<span>");
            root64.append(nodes62);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp176, tmp177, tmp178, function(_, callback) {
              var root69 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var tmp172 = mobl.ref("wachtwoord");
              
              
              var tmp175 = mobl.ref(null);
              
              
              var tmp174 = mobl.ref(null);
              
              
              var tmp173 = mobl.ref(null);
              
              var nodes63 = $("<span>");
              root69.append(nodes63);
              subs__.addSub((mobl.ui.generic.passwordField)(CivitatsMobiel.ww, tmp172, tmp173, mobl.ref(mobl.ui.generic.textFieldStyle), tmp174, tmp175, function(_, callback) {
                var root70 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root70); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes63;
                nodes63 = node.contents();
                oldNodes.replaceWith(nodes63);
              }));
              callback(root69); return subs__;
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes62;
              nodes62 = node.contents();
              oldNodes.replaceWith(nodes62);
            }));
            
            var tmp183 = mobl.ref(false);
            
            
            var tmp182 = mobl.ref(null);
            
            
            var tmp181 = mobl.ref(null);
            
            var nodes64 = $("<span>");
            root64.append(nodes64);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp181, tmp182, tmp183, function(_, callback) {
              var root71 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var tmp180 = mobl.ref(function(event, callback) {
                                   if(event && event.stopPropagation) event.stopPropagation();
                                   var result__ = mobl.alert("hoi");
                                   if(callback && callback.apply) callback(); return;
                                 });
              
              
              var tmp179 = mobl.ref("alert");
              
              var nodes65 = $("<span>");
              root71.append(nodes65);
              subs__.addSub((mobl.ui.generic.button)(tmp179, mobl.ref(mobl.ui.generic.buttonStyle), mobl.ref(mobl.ui.generic.buttonPushedStyle), tmp180, function(_, callback) {
                var root72 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root72); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes65;
                nodes65 = node.contents();
                oldNodes.replaceWith(nodes65);
              }));
              callback(root71); return subs__;
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes64;
              nodes64 = node.contents();
              oldNodes.replaceWith(nodes64);
            }));
            
            var tmp188 = mobl.ref(false);
            
            
            var tmp187 = mobl.ref(null);
            
            
            var tmp186 = mobl.ref(null);
            
            var nodes66 = $("<span>");
            root64.append(nodes66);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp186, tmp187, tmp188, function(_, callback) {
              var root73 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var tmp185 = mobl.ref(function(event, callback) {
                                   if(event && event.stopPropagation) event.stopPropagation();
                                   var result__ = mobl.alert(CivitatsMobiel.ww.get() + " " + CivitatsMobiel.loginNaam.get());
                                   mobl.call('CivitatsMobiel.root', [mobl.ref(false), mobl.ref("slide")], function(result__) {
                                   var tmp221 = result__;
                                   if(callback && callback.apply) callback(); return;
                                   });
                                 });
              
              
              var tmp184 = mobl.ref("inloggen");
              
              var nodes67 = $("<span>");
              root73.append(nodes67);
              subs__.addSub((mobl.ui.generic.button)(tmp184, mobl.ref(mobl.ui.generic.buttonStyle), mobl.ref(mobl.ui.generic.buttonPushedStyle), tmp185, function(_, callback) {
                var root74 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root74); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes67;
                nodes67 = node.contents();
                oldNodes.replaceWith(nodes67);
              }));
              callback(root73); return subs__;
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes66;
              nodes66 = node.contents();
              oldNodes.replaceWith(nodes66);
            }));
            callback(root64); return subs__;
            
            
            
            
            
            return subs__;
          }, function(node) {
            var oldNodes = nodes57;
            nodes57 = node.contents();
            oldNodes.replaceWith(nodes57);
          }));
          
          
        } else {
          
        }
      };
      renderCond5();
      subs__.addSub(tmp189.addEventListener('change', function() {
        renderCond5();
      }));
      
      callback(root61); return subs__;
      
      
      return subs__;
    }, function(node) {
      var oldNodes = nodes54;
      nodes54 = node.contents();
      oldNodes.replaceWith(nodes54);
    }));
    callback(root60); return subs__;
    
    
    return subs__;
  }, function(node) {
    var oldNodes = nodes53;
    nodes53 = node.contents();
    oldNodes.replaceWith(nodes53);
  }));
  callback(root58); return subs__;
  
  
  
  return subs__;
};

CivitatsMobiel.maaltijden = function(callback, screenCallback) {
  var root75 = $("<div>");
  var subs__ = new mobl.CompSubscription();
  
  var tmp194 = mobl.ref("meld je aan of af voor een maatlijd");
  
  
  var tmp195 = mobl.ref(null);
  
  var nodes68 = $("<span>");
  root75.append(nodes68);
  subs__.addSub((mobl.ui.generic.header)(tmp194, tmp195, function(_, callback) {
    var root76 = $("<span>");
    var subs__ = new mobl.CompSubscription();
    
    var tmp192 = mobl.ref(function(event, callback) {
                         if(event && event.stopPropagation) event.stopPropagation();
                         mobl.call('CivitatsMobiel.root', [mobl.ref(false), mobl.ref("slide")], function(result__) {
                         var tmp222 = result__;
                         if(callback && callback.apply) callback(); return;
                         });
                       });
    
    
    var tmp193 = mobl.ref(mobl._("Back", []));
    
    var nodes69 = $("<span>");
    root76.append(nodes69);
    subs__.addSub((mobl.ui.generic.backButton)(tmp193, mobl.ref(mobl.ui.generic.backButtonStyle), mobl.ref(mobl.ui.generic.backButtonPushedStyle), tmp192, function(_, callback) {
      var root77 = $("<span>");
      var subs__ = new mobl.CompSubscription();
      callback(root77); return subs__;
      return subs__;
    }, function(node) {
      var oldNodes = nodes69;
      nodes69 = node.contents();
      oldNodes.replaceWith(nodes69);
    }));
    callback(root76); return subs__;
    
    return subs__;
  }, function(node) {
    var oldNodes = nodes68;
    nodes68 = node.contents();
    oldNodes.replaceWith(nodes68);
  }));
  
  var aj = mobl.ref(null);
  CivitatsMobiel.Maal.next10(function(result__) {
    var tmp223 = result__;
    var result__ = tmp223;
    aj.set(result__);
    
  });
  
  var tmp204 = mobl.ref("Loading...");
  
  var nodes70 = $("<span>");
  root75.append(nodes70);
  subs__.addSub((mobl.ui.generic.whenLoaded)(aj, mobl.ref(mobl.ui.generic.loadingStyle), tmp204, function(_, callback) {
    var root78 = $("<span>");
    var subs__ = new mobl.CompSubscription();
    
    var node8 = mobl.loadingSpan();
    root78.append(node8);
    var list2;
    var listSubs__ = new mobl.CompSubscription();
    subs__.addSub(listSubs__);
    var renderList2 = function() {
      var subs__ = listSubs__;
      list2 = aj.get();
      list2.list(function(results2) {
        node8.empty();
        for(var i2 = 0; i2 < results2.length; i2++) {
          (function() {
            var iternode2 = $("<span>");
            node8.append(iternode2);
            var maaltijd;
            maaltijd = mobl.ref(mobl.ref(results2), i2);
            
            var tmp203 = mobl.ref(false);
            
            
            var tmp202 = mobl.ref(null);
            
            
            var tmp201 = mobl.ref(null);
            
            var nodes71 = $("<span>");
            iternode2.append(nodes71);
            subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp201, tmp202, tmp203, function(_, callback) {
              var root79 = $("<span>");
              var subs__ = new mobl.CompSubscription();
              
              var date = mobl.ref(mobl.DateTime.fromTimestamp(maaltijd.get().datum * 1000));
              
              var tmp196 = mobl.ref(date.get().getDate() + "-" + date.get().getMonth() + "-" + date.get().getFullYear() + " " + maaltijd.get().tekst);
              subs__.addSub(date.addEventListener('change', function() {
                tmp196.set(date.get().getDate() + "-" + date.get().getMonth() + "-" + date.get().getFullYear() + " " + maaltijd.get().tekst);
              }));
              subs__.addSub(mobl.ref(maaltijd, 'tekst').addEventListener('change', function() {
                tmp196.set(date.get().getDate() + "-" + date.get().getMonth() + "-" + date.get().getFullYear() + " " + maaltijd.get().tekst);
              }));
              
              
              var tmp198 = mobl.ref(null);
              
              
              var tmp197 = mobl.ref(null);
              
              var nodes72 = $("<span>");
              root79.append(nodes72);
              subs__.addSub((mobl.label)(tmp196, tmp197, tmp198, function(_, callback) {
                var root80 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root80); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes72;
                nodes72 = node.contents();
                oldNodes.replaceWith(nodes72);
              }));
              
              var tmp200 = mobl.ref(function(event, callback) {
                                   if(event && event.stopPropagation) event.stopPropagation();
                                   mobl.call('CivitatsMobiel.aanafmelden', [maaltijd, mobl.ref(false), mobl.ref("slide")], function(result__) {
                                   var tmp224 = result__;
                                   if(callback && callback.apply) callback(); return;
                                   });
                                 });
              
              
              var tmp199 = mobl.ref(CivitatsMobiel.tekstKnopMaaltijd(maaltijd.get()));
              subs__.addSub(maaltijd.addEventListener('change', function() {
                tmp199.set(CivitatsMobiel.tekstKnopMaaltijd(maaltijd.get()));
              }));
              
              var nodes73 = $("<span>");
              root79.append(nodes73);
              subs__.addSub((mobl.ui.generic.sideButton)(tmp199, mobl.ref(mobl.ui.generic.sideButtonStyle), mobl.ref(mobl.ui.generic.sideButtonPushedStyle), tmp200, function(_, callback) {
                var root81 = $("<span>");
                var subs__ = new mobl.CompSubscription();
                callback(root81); return subs__;
                return subs__;
              }, function(node) {
                var oldNodes = nodes73;
                nodes73 = node.contents();
                oldNodes.replaceWith(nodes73);
              }));
              callback(root79); return subs__;
              
              
              return subs__;
            }, function(node) {
              var oldNodes = nodes71;
              nodes71 = node.contents();
              oldNodes.replaceWith(nodes71);
            }));
            
            var oldNodes = iternode2;
            iternode2 = iternode2.contents();
            oldNodes.replaceWith(iternode2);
            
            
          }());
        }
        mobl.delayedUpdateScrollers();
        subs__.addSub(list2.addEventListener('change', function() { listSubs__.unsubscribe(); renderList2(true); }));
        subs__.addSub(aj.addEventListener('change', function() { listSubs__.unsubscribe(); renderList2(true); }));
      });
    };
    renderList2();
    
    callback(root78); return subs__;
    
    return subs__;
  }, function(node) {
    var oldNodes = nodes70;
    nodes70 = node.contents();
    oldNodes.replaceWith(nodes70);
  }));
  callback(root75); return subs__;
  
  
  
  return subs__;
};

CivitatsMobiel.aanafmelden = function(maaltijd, callback, screenCallback) {
  var root82 = $("<div>");
  var subs__ = new mobl.CompSubscription();
  
  var tmp207 = mobl.ref("aan/af melden");
  
  
  var tmp208 = mobl.ref(null);
  
  var nodes74 = $("<span>");
  root82.append(nodes74);
  subs__.addSub((mobl.ui.generic.header)(tmp207, tmp208, function(_, callback) {
    var root83 = $("<span>");
    var subs__ = new mobl.CompSubscription();
    
    var tmp205 = mobl.ref(function(event, callback) {
                         if(event && event.stopPropagation) event.stopPropagation();
                         mobl.call('CivitatsMobiel.maaltijden', [mobl.ref(false), mobl.ref("slide")], function(result__) {
                         var tmp225 = result__;
                         if(callback && callback.apply) callback(); return;
                         });
                       });
    
    
    var tmp206 = mobl.ref(mobl._("Back", []));
    
    var nodes75 = $("<span>");
    root83.append(nodes75);
    subs__.addSub((mobl.ui.generic.backButton)(tmp206, mobl.ref(mobl.ui.generic.backButtonStyle), mobl.ref(mobl.ui.generic.backButtonPushedStyle), tmp205, function(_, callback) {
      var root84 = $("<span>");
      var subs__ = new mobl.CompSubscription();
      callback(root84); return subs__;
      return subs__;
    }, function(node) {
      var oldNodes = nodes75;
      nodes75 = node.contents();
      oldNodes.replaceWith(nodes75);
    }));
    callback(root83); return subs__;
    
    return subs__;
  }, function(node) {
    var oldNodes = nodes74;
    nodes74 = node.contents();
    oldNodes.replaceWith(nodes74);
  }));
  
  var aj = mobl.ref(null);
  CivitatsMobiel.aanAfmelden(maaltijd.get(), function(result__) {
    var tmp226 = result__;
    var result__ = tmp226;
    aj.set(result__);
    
  });
  
  var tmp215 = mobl.ref("Loading...");
  
  var nodes76 = $("<span>");
  root82.append(nodes76);
  subs__.addSub((mobl.ui.generic.whenLoaded)(aj, mobl.ref(mobl.ui.generic.loadingStyle), tmp215, function(_, callback) {
    var root85 = $("<span>");
    var subs__ = new mobl.CompSubscription();
    
    var tmp214 = mobl.ref(false);
    
    
    var tmp213 = mobl.ref(null);
    
    
    var tmp212 = mobl.ref(null);
    
    var nodes77 = $("<span>");
    root85.append(nodes77);
    subs__.addSub((mobl.ui.generic.item)(mobl.ref(mobl.ui.generic.itemStyle), mobl.ref(mobl.ui.generic.itemPushedStyle), tmp212, tmp213, tmp214, function(_, callback) {
      var root86 = $("<span>");
      var subs__ = new mobl.CompSubscription();
      
      var tmp209 = mobl.ref(aj.get().toString());
      subs__.addSub(aj.addEventListener('change', function() {
        tmp209.set(aj.get().toString());
      }));
      
      
      var tmp211 = mobl.ref(null);
      
      
      var tmp210 = mobl.ref(null);
      
      var nodes78 = $("<span>");
      root86.append(nodes78);
      subs__.addSub((mobl.label)(tmp209, tmp210, tmp211, function(_, callback) {
        var root87 = $("<span>");
        var subs__ = new mobl.CompSubscription();
        callback(root87); return subs__;
        return subs__;
      }, function(node) {
        var oldNodes = nodes78;
        nodes78 = node.contents();
        oldNodes.replaceWith(nodes78);
      }));
      callback(root86); return subs__;
      
      return subs__;
    }, function(node) {
      var oldNodes = nodes77;
      nodes77 = node.contents();
      oldNodes.replaceWith(nodes77);
    }));
    callback(root85); return subs__;
    
    return subs__;
  }, function(node) {
    var oldNodes = nodes76;
    nodes76 = node.contents();
    oldNodes.replaceWith(nodes76);
  }));
  callback(root82); return subs__;
  
  
  
  return subs__;
};
