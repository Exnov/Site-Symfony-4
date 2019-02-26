$(function(){
//DEBUT CODE

    /*
    **********************************************************************************************************
    MENU 
    1-définition d'objets :
    2-Création d'objets et gestion d'events:
    ***********************************************************************************************************
    */

    /*
    **********************************************************************************************************
    1-définition d'objets :
    -1. SearchBarAutoCplt : barre de recherche d'auto-complétion
    -2. ListenerInputImage : affichage et disparition d'un bouton de suppression d'image pour un input file
    -3. Visual : gestion de l'affichage des images dans les forms de création/edition de News, Music, Films
    -4. AjaxRemove : gestion de l'affichage des images dans des formulaires de l'administration; lié à SuperVisual
    -5. SuperVisual : Gestion de l'affichage des images pour fes forms de admin pour : homepage, background, logos
    -6. NavbarColorLink : Gestion de la colorisation du a selectionné dans un navbar 
    ***********************************************************************************************************
    */

	/*
	**********************************************************
	1.1.
	SearchBarAutoCplt :
	**********************************************************
	 */
    var SearchBarAutoCplt={

      	init:function(formulaire,inputSearch,routeAjax,resultats,baliseEnfants,routeValidation,validation){ 

	        this.formulaire=formulaire;
	        this.inputSearch=inputSearch;
	        this.routeAjax=routeAjax;
	        this.resultats=resultats;
	        this.baliseEnfants=baliseEnfants;
	        this.routeValidation=routeValidation;
	        this.validation=validation;
	        //--       
			this.n=0; //position de l'user dans les divs enfants de la div results
			this.saisie=""; //memo de la saisie dans l'input
			this.codeClavier=0; //code clavier
			this.longueur=0; //nombre de divs enfants de la div results
			this.enfants; //les div de results : et écoute des enfants
			//--
			this.soumissionOffForm(); //on désactive la soumission du formulaire
			this.listenInputSearch(); //on active l'écoute des saisies sur l'iput de type search
      	},

      	soumissionOffForm:function(){  //on désactive la soumission du formulaire
			this.formulaire.addEventListener("submit",function(e){ 
				e.preventDefault();
			});
      	},

      	listenInputSearch:function(){ //on écoute les saisies de clavier dans l'input search

	      	var objet=this;

			this.inputSearch.addEventListener('keyup',function(e){

				//pour le bouton de chercher profil, on compléte sa route, et on l'associe à son href
				objet.validation.href=objet.routeValidation+this.value;
				//on réinitilise n à 0 quand la saisie de le input change
				if(this.value!==objet.saisie){
					objet.n=0;
				}
				objet.saisie=this.value;

				//------------------------------------------------------
		        if(e.keyCode!=38 && e.keyCode!=40){ //requête ajax

		            $.post( objet.routeAjax.href, { username : this.value }) 
		            .done(function( data ) {

						var users = data.users;  //array d'users 

						$(objet.resultats).empty();
						$(objet.resultats).css("border","none");
						users.forEach(function(user){
							div=document.createElement('div');
							div.textContent=user;
							objet.resultats.appendChild(div);
						});

						//les div de results : et écoute des enfants
						objet.enfants=document.querySelectorAll(objet.baliseEnfants); 

						objet.longueur=$(objet.enfants).length;
						if(objet.longueur>0){
							$(objet.resultats).css("borderLeft","1px solid black").css("borderRight","1px solid black").css("borderBottom","1px solid black");	
						}				
			
						//gestion de la selection d'un user  par clavier (gauche, droite, et entrée, 37, 39, 13)
						objet.keyboard(e.keyCode);				

						//--------------------------------------------------------------------------------------
						//gestion de la selection d'un user par souris 
						$(objet.enfants).hover(
							function(e){
								var couleur=$(this).css("backgroundColor");
								if(couleur!='rgb(20, 154, 128)'){ 
									$(this).css("backgroundColor",'#3498DB'); 
								}
							},
							function(e){
								var couleur=$(this).css("backgroundColor");
								if(couleur!='rgb(20, 154, 128)'){ 
									$(this).css("backgroundColor",'white');
								}
								
							}
						);		

						objet.enfants.forEach(function(enfant){

							enfant.addEventListener("click",function(e){
							
								$(objet.enfants).css("backgroundColor","white"); //enlever la couleur de selection pour le p qui aurait été selectionné auparavant
								$(this).css("backgroundColor","#149a80");

								objet.afterSelectionValue(enfant);
							});
						});
						//-------------------------
		            });
		         } //fin if
		        else{
		         	//les div de results : et écoute des enfants
					objet.enfants=document.querySelectorAll(objet.baliseEnfants); 

		         	//fleche bas 
		         	$(objet.enfants).each(function(index, elt){
		         		$(elt).css("backgroundColor","white");
		         		});

		         	objet.keyboard(e.keyCode); //fleche bas, haut (40,38)
		        } //fin else
			});	//fin listener inputSearch
   		}, //fin listenInputSearch

	    afterSelectionValue:function(elt){ //fonction appelée dans listenInputSearch(), quand clique ou entrée sur un des resultats
								
			this.inputSearch.value=$(elt).text(); //on affiche dans l'input l'user selectionné
			this.inputSearch.focus(); //on remet le focus sur l'input
			$(this.resultats).css("border","none");
			$(this.resultats).empty(); //on ferme les div de la results
			//pour le bout de chercher profil, on compléte sa route, et on l'associe à son href
			this.validation.href=this.routeValidation+this.inputSearch.value;
	    },


	    keyboard:function(code){ //fonction appelée dans listenInputSearch(), prend un paramètre (e.keyCode) le code de la touche, et affecte une action associée
			//gestion de la selection d'un user  par clavier --------------------------------------							
			if(code==37 || code==39){ //gauche/droite
				this.n=0;
				this.codeClavier=40; //on remet le code du bas, si ==38, en cas de retour, la nouvelle sélection serait en bas de la liste
			}

			//touche entrée
			if(code==13){

			 	x=0;
			 	if(this.codeClavier==40){
			 		x=this.n-1;
			 	}
			 	if(this.codeClavier==38){
			 		x=this.n+1;
			 	}		
			 	this.n=0; //on réinitialise		
			 	this.codeClavier=40; 
			 	//--
			 	this.afterSelectionValue($(this.enfants)[x]);		 	
			}	
			//fleche bas
			if(code==40){

				if(this.codeClavier==38){
					this.n+=2;
				}
				this.codeClavier=40;

				if(this.n==0){
					$($(this.enfants)[0]).css("backgroundColor","#149a80");
				}

				else{
					if(this.n<this.longueur){
						$($(this.enfants)[this.n]).css("backgroundColor","#149a80");
					}
					else{
						$($(this.enfants)[0]).css("backgroundColor","#149a80");
						this.n=0;
					}						
				}
				this.n+=1;
			}

			//fleche haut 
			if(code==38){
				if(this.codeClavier==40){
					this.n-=2;
				}
				this.codeClavier=38;

				if(this.n>=0){
					$($(this.enfants)[this.n]).css("backgroundColor","#149a80");
				}

				else{
					$($(this.enfants)[this.longueur-1]).css("backgroundColor","#149a80");
					this.n=this.longueur-1;
				}
				this.n-=1;							
			}		 
	    },

    };

	/*
	**********************************************************
	1.2.
	ListenerInputImage :
	**********************************************************
	*/
  	var ListenerInputImage={

	    init:function(inputImage){ 

	        this.inputImage=inputImage;
	        //on lance l'écoute
	        this.listenChange(); 
	    },

	    listenChange:function(){

	      	var objet=this;

	      	$(this.inputImage).change(function(e){

				//construction du bouton de suppression de l'image du form :
				//si le bouton existe déjà on le supprime
				if ($("#supp-avatar").length) {
				    $("#supp-avatar").remove();			 
				}
				var parent=$(this).parent();
				var labelPrev=parent.prev();
				$(labelPrev).after( '<button type="button" class="btn btn-danger ml-2 mb-2" id="supp-avatar"><i class="far fa-trash-alt"></i></button>');

				//écoute du bouton de suppression :
				objet.listenRemoveButton();		
	      	});
	    },

	    listenRemoveButton:function(){

      		var objet=this;
      		var suppAvatar=document.querySelector('#supp-avatar');

			$(suppAvatar).click(function(e){		
				$(suppAvatar).remove();
			});
	    },
   	};


	/*
	**********************************************************
	1.3.
	Visual :
	**********************************************************
	*/
   	var Visual={

      	init:function(apercu,input){ 
	        this.apercu=apercu;
	        this.input=input;
	        this.listenChange();
      	},

      	listenChange:function(){

      		var objet=this;
	        this.input.addEventListener("change",function(){ 
	            var file = this.files[0];
	            objet.createVisual(file);
	        }); 
      	},

     	createVisual:function(file){ //appelée dans listenChange()
 
	        var objet=this;
	        var reader = new FileReader();
	        reader.onload = function() {
	            // Affichage de l'image
	            objet.apercu.src = this.result;
	        };
	        reader.readAsDataURL(file);
      	},
    };

    /*
	**********************************************************
	1.4.
	AjaxRemove : 
	**********************************************************
	*/  
  	var AjaxRemove={

      	init:function(link){ 
			this.link=link;
			this.remove();
		},

		remove:function(){

			var objet=this;

			this.link.addEventListener('click',function(e){

				e.preventDefault();
	            var url=this.href;

	            $.ajax({ 
				    url: url,
				    type: 'GET',
				    success: function(data){ 

				    	//on selectionne les elts à supprimer
				    	var a=document.querySelector('#supp-'+data.key); //le a qui contient le remove
				    	var apercu=document.querySelector('#apercu-'+data.key);
				    	//on les supprime
				    	a.remove();
				    	apercu.remove();				
				    },
				    error: function(data) {

				    }
				});
			}); 
		},		
	}	  

	/*
	**********************************************************
	1.5.
	SuperVisual :
	**********************************************************
	*/
  	var SuperVisual={

      	init:function(input,url){ 
			this.input=input;
			this.regexMaster=/^master_/; 
			this.idCommun=this.getId();
			this.frameApercu=this.getFrameApercu();	
			this.apercu=this.getApercu();	
			this.parentInput=this.getParentInput();
			this.url=url;
			this.remove=this.getRemove();					
      	},

     	 createVisual:function(file){

     	 	//on vérifie si l'elt à afficher est une image :
     	 	var baliseElt=null;
     	 	if(this.apercu){
     	 		baliseElt = this.apercu.tagName; /*IMG ou p*/
     	 	}

     	 	var regex = /^image/;
     	 	var typeFile = file['type']; /* image/xxx ou autre */
			var checkImage = typeFile.match(regex);    	 	


			//chargement d'img ou  de p			
			if(checkImage){

		        var objet=this;
		        var reader = new FileReader();
		        reader.onload = function() {
		            // Affichage de l'image
					if(baliseElt==='P'){
						$(objet.apercu).replaceWith( '<img class="imgpreview" src="'+this.result+'"/>');
					}		
					else if(baliseElt==='IMG'){
						objet.apercu.src = this.result;
					} 
					//si pas de balise et chargement de media, on recrée objet.apercu
					else{ 
						//creation de l'apercu img
						var img=objet.createApercuImg(this.result);
						//creation du bloc media avec apercu img, sa div, et la div pour remove
						objet.createBlocMedia(img);						
					}					           	            
		        };
		        reader.readAsDataURL(file);					
			}

			//pas de chargement d'image et modification à faire
			else if(checkImage===null){

				if(baliseElt==='IMG'){
					$(this.apercu).replaceWith( '<p class="text-white"><i class="fas fa-video pr-2"></i> média enregistré</p>');
				}

				else if(baliseElt===null){
					//creation de l'apercu p
					var p=this.createApercuP();
					//creation du bloc media avec apercu p, sa div, et la div pour remove
					this.createBlocMedia(p);								
				}							
			}
      	},

      	createBlocMedia:function(apercu){ //comprend la construction du bloc apercuImg/apercuP et du blc remove

			//creation du container de l'apercu : div apercu
			var div=this.createFrameApercu(this.idCommun);

			//on ajout img a div
			div.appendChild(apercu); //apercu, soit pour img ou p

			//creation du a remove
			var aRemove=this.createRemove(this.idCommun);					

			//on ajoute les elts
			$(this.parentInput).after(div);
			//on recupere le label pour placer juste après le bouton de suppression
			var labelInput=this.parentInput.parentNode.firstElementChild;
			$(labelInput).after(aRemove);

			//on attribue au nouveau a (qui représente le bouton de suppression) le listener d'origine pour suppression via ajax.
			this.remove=this.getRemove();
			var ajaxRemove=Object.create(AjaxRemove);
	        ajaxRemove.init(this.remove); 			
      	},

      	createApercuImg:function(result){
      		//img
			var img='';
			img=document.createElement('img');
			img.setAttribute('class','imgpreview');
			img.setAttribute('src',result);

			return img;
      	},

      	createApercuP:function(){
      		//p
			var p=document.createElement('p');
			p.setAttribute('class','text-white');
			p.textContent='média enregistré';
			//i
			var i=document.createElement('i');
			i.setAttribute('class','fas fa-video pr-2');
			
			$(p).prepend(i);

			return p;
      	},


      	createFrameApercu:function(){
			//div qui contient l'apercu
			var div=document.createElement('div');
			div.setAttribute('id','apercu-'+this.idCommun); 
			div.setAttribute('class','apercu');  
			
			return div;   		
      	},

      	createRemove:function(idCommun){
			//--a
			var a=document.createElement('a');
			a.setAttribute('id','supp-'+idCommun); 
			a.setAttribute('class','supp-media');
			//---
			//pour background : 
			if(idCommun.match(this.regexMaster)){
				//on recupère soit 0,1,2
				var index=idCommun.length-7;
				idCommun=idCommun.charAt(index)
			}
			//---
			a.setAttribute('href',this.url+idCommun+'/remove');
			//--i
			var i=document.createElement('i');
			i.setAttribute('class','far fa-trash-alt'); 

			a.appendChild(i);
			
			return a;   		
      	},   

      	getId:function(){
			var idInput=$(this.input).attr('id');
			//pour homepage
			if(idInput.match(this.regexMaster)===null){
				var indexSep = idInput.indexOf('_');
				var id=idInput.substring(indexSep+1);
				return id
			}	

			return idInput;
      	},	

      	getFrameApercu:function(){
      		var frameApercu=document.querySelector('#apercu-'+this.idCommun);
      		return frameApercu;
      	},

      	getApercu:function(){
      		if(this.frameApercu){
      			var apercu=this.frameApercu.firstElementChild;
      			return apercu;
      		}
      		return null;   		
      	},

      	getParentInput:function(){
			var parent=this.input.parentNode;
			return parent; 		
      	}, 

      	getRemove:function(){
      		var a=document.querySelector('#supp-'+this.idCommun);
      		return a;	
      	},     

    };

	/*
	**********************************************************
	1.6.
	NavbarColorLink :
	**********************************************************
	*/    
	var NavbarColorLink={

	    init:function(menu,refLink,color,colorDropDown){ 
	        this.menu=menu;
	        this.refLink=refLink;
	        this.links=[];
	        this.inside;
	        this.colorLink=color;
	        this.colorLinkDown=colorDropDown;
	        this.colorSelectedLink();
	    },

		colorSelectedLink:function(){
	        var objet=this;
	        this.inside=false; //pour gérer les a qui n'appartiendraient pas aux navbars
	        //--
	        this.menu.forEach(function(link){

	          	objet.links.push(link.href);
	          	if(link.href==objet.refLink){
	                //si link est un a dans un sous-menu
	                objet.manageColorDropdonwItem(link);              
	              	objet.inside=true;
	          	}

	        });
	        //--
	        this.manageOutsideLink();
	    },

	    manageOutsideLink:function(){ //appelée dans colorSelectedLink
	        //on identifie le a dans le navbar, qui correspond à la page en cours et dont le a se trouve à l'extérieur des navbars
	        if(this.inside==false){ 
	          	var index;

	          	//regex pour choper l'index du a à colorier
	          	for (var i = 0; i < this.links.length; i++) {

					//--cas particulier de films et film :
					if(this.links[i].includes('films')){ //si films dans link on supprime son 's' qui est sa derniere lettre
						this.links[i]=this.links[i].substring(0,this.links[i].length-1);
					}            

	            	var regex=new RegExp('^'+this.links[i]);

	            	if(window.location.href.match(regex)){
	                	if(i>0){ //on ne prend pas 0 qui correspond à la page d'accueil
	                  		index=i;
	                	}
	            	}
	          	}
	          	//on colore le a correspondant si index est défini:
	          	if(index){
	          		//si link est un a dans un sous-menu
	          		this.manageColorDropdonwItem(this.menu[index]);
	          	}	          
	        }
	    },

	    manageColorDropdonwItem:function(link){ //appelée dans colorSelectedLink et manageOutsideLink
                //si link est un a dans un sous-menu
                if($(link).hasClass('dropdown-item')){
                    //on colore aussi le lien parent qui apparait dans la navbar
                    var parentLink=link.parentNode.parentNode.childNodes[1];
	                $(parentLink).css('color',this.colorLink);     

                    var pathname=window.location.pathname;
                    if(pathname!=='/profil/mail/expedition'){
	               	 	$(link).css('color',this.colorLinkDown);
                    }
                    else{ // cas particulier des messages envoyés
                    	$('a[href="/profil/mail/reception"]').css('color',this.colorLinkDown);
                    }
              	}
              	else{
              		$(link).css('color',this.colorLink);
              	}
	    },

	};


   /*
   **********************************************************************************************************
   2-Création d'objets et gestion d'events:
   -1. Gestion de la barre de recherche des users pour l'administrateur 
   -2. Gestion des likes pour les commentaires dans articles News, Film, Music, et dans les forums
   -3. Gestion de l'affichage et disparition d'un bouton de suppression d'image pour avatar dans Profil
   -4. Gestion du retour arrière via navigateur quand mail lu pour la 1ère fois
   -5. Gestion de l'affichage de l'image lors de la création et de la modification d'un article News, Film, Music
   -6. Gestion de l'affichage des images dans admin pour : homepage, background, logos
   -7. Confirmation de l'envoi du formulaire : pages contact et reset password et connexion
   -8. Enregistrement ajax pour : admin social network et admin seo
   -9. Gestion de la navbar sticky en homepage
   -10. Gestion autorm user
   -11. Gestion de la colorisation du a selectionné dans les navbar principales
   -12. Gestion de l'affichage du footer pour qu'il reste en bas de page
   ***********************************************************************************************************
   */

    /*
    **********************************************************
    2.1.
    Gestion de la barre de recherche des users pour l'administrateur 
    -création de l'objet SearchBarAutoCplt : 
    **********************************************************
    */
	if(document.querySelector('#search-user')){ //form

		var formulaire=document.querySelector('#search-user'); //form
		var inputSearch=document.querySelector('#search-user-input'); //input search
		var routeAjax=document.querySelector('#route-search'); //bouton de validation de l'user à rechercher, permet de récupérer le lien vers le traitement php de la requête ajax	
		var resultats=document.querySelector('#results'); //div qui contiendra les resultats
		var baliseEnfants='#results div'; //contiendra tous les divs enfants de #results
		//--
		var u = routeAjax.href.indexOf('user'); //renvoie l'index de 'u' de 'user' dans route.href
		var routeValidation = routeAjax.href.substring(0, u)+"profil/search/"; //on découpe la base de la route, de façon à construire celle pour le bouton de validation	
		//--
		var validation=document.querySelector("#validation-search"); //bouton de recherche des infos de l'utilisateur

        //création objet
        var searchBarAdmin=Object.create(SearchBarAutoCplt); 
        searchBarAdmin.init(formulaire,inputSearch,routeAjax,resultats,baliseEnfants,routeValidation,validation);		
		
	}

    /*
    **********************************************************
    2.2.
    Gestion des likes pour les commentaires dans articles News, Film, Music, et dans les forums
    **********************************************************
    */
	
	if(document.querySelectorAll('.reaction-like')){ //<a>

		document.querySelectorAll('.reaction-like').forEach(function(link){ //<a>

			link.addEventListener('click',function(e){

				e.preventDefault();

                var url=this.href;
                var spanCount=this.querySelector('.reaction-likes'); //<span>
                var icone=this.querySelector("i");

                $.ajax({ 
				    url: url,
				    type: 'GET',
				    success: function(data){ 
				        //maj du nombre de likes
	                    spanCount.textContent=data.likes;
	                    //maj de l'icône, on verifie sa classe pour savoir quelle icône mettre
	                    if(icone.classList.contains("fas")){
	                        icone.classList.replace("fas","far");
	                    }
	                    else{
	                        icone.classList.replace("far","fas");
	                    }
				    },
				    error: function(data) {
				        //$('#myModal').modal('show');
				    }
				});          
			});
		});
	}

    /*
    **********************************************************
    2.3.
    Gestion de l'affichage et disparition d'un bouton de suppression d'image pour avatar dans Profil : 
    **********************************************************
    */
	if(document.querySelector('#profil_image')){

	   //Création de l'objet ListenerInputImage : 		
		var inputImage=document.querySelector('#profil_image');
        //création objet
        var listen=Object.create(ListenerInputImage); 
        listen.init(inputImage);		
	}        

    /*
    **********************************************************
    2.4.
    Gestion du retour arrière via navigateur d'un mail lu pour la 1ère fois : 
    **********************************************************
    */		
	//dans Mon compte > messagerie : pour retirer le marquage du message lu quand retour sur la page du tableau des 
	//messages reçus via le bouton de retour du navigateur.
	if(document.querySelectorAll('.mail-title')){
	
		var titles=document.querySelectorAll('.mail-title');

		$(titles).each(function(index,title){

			$(title).click(function(e){
				
				if($(title).parent().hasClass('marker-mail')){ 
					sessionStorage.setItem('index', index);
				}

			});

		});

		if(sessionStorage.getItem("index")){
				var index=sessionStorage.getItem("index");
				var title=$($(titles)[index]);

				if(title.parent().hasClass('marker-mail')){ 

					sessionStorage.clear();
					window.location.reload();
				}
		}

	}

   /*
   ************************************************************************************
   2.5.
   Gestion de l'affichage de l'image lors de la création et de la modification d'un article News, Film, Music :
   - Création d'objets Visual
   ************************************************************************************
   */
 	if(document.querySelector('#news_image') || document.querySelector('#film_image') || document.querySelector('#music_image')){

		var image=document.querySelector("#imgpreview");
  		var input, idInput;	

  		if(document.querySelector('#news_image')){
  			idInput='#news_image';
  		}
  		else if(document.querySelector('#film_image')){
  			idInput='#film_image';
  		}
  		else if(document.querySelector('#music_image')){
  			idInput='#music_image';
  		} 

  		input=document.querySelector(idInput); 

   		//Création de l'objet Visual : 
        var visualisator=Object.create(Visual); 
        visualisator.init(image,input);   		
	}   


   /*
   ************************************************************************************
   2.6.
   Gestion de l'affichage des images dans admin pour : homepage, background, logos :
   - Création d'objets SuperVisual et AjaxRemove
   ************************************************************************************
   */	
	if(document.querySelector('#admin-homepage') || document.querySelector('#admin-background') || document.querySelector('#admin-logos')){

  		//ecoute des inputs pour l'importation de fichiers  		
  		var inputs=document.querySelectorAll(".custom-file input"); 
  		var url= ''; 

  		if(document.querySelector('#admin-homepage')){
  			url= '/admin/homepage/';
  			indiceId='homepage';
  		}
  		else if(document.querySelector('#admin-background')){
  			url= '/admin/background/';
  		}
  		else if(document.querySelector('#admin-logos')){
  			url= '/admin/logos/';
  		}  		

  		inputs.forEach(function(input){

	        input.addEventListener("change",function(e){ 
	            var file = this.files[0];
			    // Création de l'objet SuperVisual :     
	            var visualisator=Object.create(SuperVisual); 
	        	visualisator.init(input,url); 
	            visualisator.createVisual(file);
	        }); 
	        
  		});

  		//ecoute des boutons de suppression
		document.querySelectorAll('.supp-media').forEach(function(link){ //<a>
				//Création de l'objet AjaxRemove :    
				var ajaxRemove=Object.create(AjaxRemove);
			    ajaxRemove.init(link); 			

			});
	}			

    /*
    ************************************************************************************
    2.7.
    Confirmation de l'envoi du formulaire : pages contact et reset password et connexion
    ************************************************************************************
    */	
	if(document.querySelector('#envoi-confirmation')){
		var p=document.querySelector('#envoi-confirmation');
		//disparition :
		setTimeout(function(){ 
			  $(p).fadeOut( "slow", function() {
			  });
			  
		}, 3000);
	}

    /*
    ************************************************************************************
    2.8.
    Enregistrement ajax pour : admin social network et admin seo
    ************************************************************************************
    */
	if(document.querySelector('#social-admin')){

		var form=document.querySelector('#social-admin');
		var p=document.querySelector('#confirm-social');

		form.addEventListener('submit',function(e){
			e.preventDefault();

            // Récupération des champs du formulaire dans l'objet FormData
            var data = new FormData(form);			
            var url= window.location.href + '/save'; 

	         ajaxPost(url, data, function (donnees) { 
		  	    //----------------------------------
	            $(p).css('display','inline');
				setTimeout(function(){ 
					  $(p).fadeOut( "slow", function() {
					  });
					  
				}, 3000); 
				//---------------------------------- 	              	          
	         });			
		});
	}

    /*
    ************************************************************************************
    2.9.
    Gestion de la navbar sticky en homepage
    ************************************************************************************
    */
	if(document.querySelector("#navbarsticky")){

		// When the user scrolls the page, execute myFunction
		window.onscroll = function() {myFunction()};

		// Get the navbar
		var navbar = document.getElementById("navbarsticky");
		// Get the offset position of the navbar
		var sticky = navbar.offsetTop;

		// Add the sticky class to the navbar when you reach its scroll position. Remove "sticky" when you leave the scroll position
		function myFunction() {
		  if (window.pageYOffset >= sticky) {
		    navbar.classList.add("sticky")
		  } else {
		    navbar.classList.remove("sticky");
		  }
		}
	}

    /*
    ************************************************************************************
    2.10.
    Gestion autorm user
    ************************************************************************************
    */
	if(document.querySelector('#user-rm-user')){

		var button=document.querySelector('#supp-talk');
		button.addEventListener('click',function(e){
			e.preventDefault();

			var url=this.href;
			//----------------
            $.ajax({ 
			    url: url,
			    type: 'GET',
			    success: function(data){ 
			    	window.location = data.urlLogout;
			    },
			    error: function(data) {
			    	
			    }
			});
			//----------------
		});
	}

    /*
    ************************************************************************************
    2.11.
    Gestion de la colorisation du a selectionné dans les navbar principales
    ************************************************************************************
    */	
    //PARTIE public et users :
    //page Accueil
    if(document.querySelector('#navbarsticky')){
    	var linkHomepage=document.querySelector('#navbarColor01 ul li a'); //1er a dans navbarColor01 par défaut
    	$(linkHomepage).css('color','#18BC9C');
    }
    //hors page Accueil
	if(document.querySelector('#navbarclassic')){

	    var menuLinks=document.querySelectorAll('#navbarclassic #navbarColor01 ul li a'); 
	    var refLink=window.location.href;

	    //création objet
	    var navbarColor=Object.create(NavbarColorLink); 
	    navbarColor.init(menuLinks,refLink,'#18BC9C','#18BC9C');           
	}
	//PARTIE admin :
	//taille écran classique et large
	if(document.querySelector('#navbar-admin-large')){

    	var menuLinks=document.querySelectorAll('#navbar-admin-large ul li a'); 
	    var refLink=window.location.href;

	    //création objet
	    var navbarColor=Object.create(NavbarColorLink); 
	    navbarColor.init(menuLinks,refLink,'#f804a2','#18BC9C');
	}
	//taille écran petit
	if(document.querySelector('#navbar-admin-small')){

    	var menuLinks=document.querySelectorAll('#navbar-admin-small ul li a'); 
	    var refLink=window.location.href;

	    //création objet
	    var navbarColor=Object.create(NavbarColorLink); 
	    navbarColor.init(menuLinks,refLink,'black','#18BC9C');
	}

    /*
    ************************************************************************************
    2.12.
    Gestion de l'affichage du footer pour qu'il reste en bas de page
    ************************************************************************************
    */
    if(document.querySelector('#navbarclassic')){

    	//soustraction des hauteurs de body et navbar(pas intégrée dans le height de body)
    	var heightRef=$('body').height() - $('#navbarclassic').height();
    	//hauteur de la page :
    	var heightDoc=$(document).height();
    	//si la hauteur de la page dépasse du body, le footer n'est pas en bas, on le met alors en bas
    	if(heightDoc>heightRef){
    		var distance=heightDoc-heightRef;
    		$('footer').css('marginTop',distance + 'px');
    	}
    }

//FIN CODE -------------------------------------------------
});