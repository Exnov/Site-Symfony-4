$(function () {
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
    -1. HandlerGraph : recupère les données pour les graphiques, et créé les graphique via l'Objet Graph
    -2. Graph : créateur de graphiques
    ***********************************************************************************************************
    */    

	/*
	**********************************************************
	1.1.
	HandlerGraph :
	**********************************************************
	 */
    var HandlerGraph={

      init:function(dataAjax){ 

      		this.dataAjax=dataAjax;

			this.tabNbreUsers=[];
			this.tabNbreTopics=[];
			this.tabNbreReactions=[];
			this.tabNbreCreatorTopics=[];
			this.tabNbreCreatorReactions=[];
			this.tabTauxTopics=[];
			this.tabTauxReactions=[];
			this.tabTopicsByCategories=[]; //tableau de tableaux associatifs
			this.tabReactionsByCategories=[];
			this.topicsCreatorsList;
			this.reactionsCreatorsList;
			this.usersLiked;
			this.usersSignaleurs;
			this.usersSignaled;

			//---------
			this.putData();
			this.makeGraphs();

      },

      putData:function(){

	    	//Récupération des données pour graphiques et camemberts
			//--construction data part 1 :
			this.makeDataGraph1();
			//--construction data part 2 :
			this.makeDataGraph2();	
	    
			//--
			//les données pour les camemberts ne subissent pas de traitement, et sont issus directement de data de ajax
			//liste des users qui écrivent des topics :
			this.topicsCreatorsList=this.dataAjax.topicsCreatorsList;
			//liste des users qui écrivent des messages :
			this.reactionsCreatorsList=this.dataAjax.reactionsCreatorsList;
			//liste des users likés :
			this.usersLiked=this.dataAjax.usersLiked;
			//liste des users qui signalent :
			this.usersSignaleurs=this.dataAjax.usersSignaleurs;
			//liste des users signalés :
			this.usersSignaled=this.dataAjax.usersSignaled;
      },

	makeDataGraph1:function(){ //pour putData()


			for(var i=0; i<12; i++){

				var clef=i+1;
				clef=clef.toString();
				if(i<9){ //!= entre 9 et 09 ou 3 et 03
					clef='0'+clef;
				}
				this.tabNbreUsers.push(this.dataAjax.users[clef]);
				this.tabNbreTopics.push(this.dataAjax.topics[clef]);
				this.tabNbreReactions.push(this.dataAjax.reactions[clef]);

				this.tabNbreCreatorTopics.push(this.dataAjax.authorsTopics[clef]);
				this.tabNbreCreatorReactions.push(this.dataAjax.authorsReactions[clef]);	

				this.tabTauxTopics.push(this.dataAjax.topicsImplication[clef]);
				this.tabTauxReactions.push(this.dataAjax.reactionsImplication[clef]);									
			}

       },

       makeDataGraph2:function(){ //pour putData()

				var categories=Object.keys(this.dataAjax.topicsByCategory); //les catégories sont les mêmes pour les reactions
				var objet=this;
				$(categories).each(function(index,categorie){

					//topics
					objet.tabTopicsByCategories=objet.getArrayContentByCategory(objet.dataAjax.topicsByCategory, categorie, objet.tabTopicsByCategories);

					//reactions
					objet.tabReactionsByCategories=objet.getArrayContentByCategory(objet.dataAjax.reactionsByCategory, categorie, objet.tabReactionsByCategories);					
				});	
       },

       getArrayContentByCategory:function(source, category, tabRecup){ //pour makeDataGraph2()

				var tab=Object.values(source[category]);
				var tabCategory={name : category,data : tab};
				tabRecup.push(tabCategory);	
				return tabRecup;

       },

      makeGraphs:function(){
			//graph des inscriptions 
	    	var graphUsers=Object.create(Graph); 
	    	var seriesRegisters=[{
		            name: 'Nouveaux utilisateurs',
		            data: this.tabNbreUsers},
	        ];
			graphUsers.init('graphUsers', 'Nombre d\'inscriptions par mois', year, 'Inscriptions',seriesRegisters
			);
			graphUsers.line();

			//column       		
	    	//graph des créations de contenus sur forum 
	    	var graphCreaContent=Object.create(Graph); 
	    	var seriesCreaContent=[{
			        name: 'Nouveaux topics',
			        data: this.tabNbreTopics
			    }, {
			        name: 'Nouveaux messages',
			        data: this.tabNbreReactions
			    }];
			graphCreaContent.init('graphForum', 'Evolution mensuelle de la création de contenus sur le forum', year, 'Quantité', 
				seriesCreaContent
			);
			graphCreaContent.column();

	    	//graph du nombre de créateur sur forum
	    	var graphCreator=Object.create(Graph); 
	    	var seriesCreator=[{
			        name: 'Créateurs topics',
			        data: this.tabNbreCreatorTopics
			    }, {
			        name: 'Créateurs messages',
			        data: this.tabNbreCreatorReactions
			    }];
			graphCreator.init('graphCreator', 'Evolution mensuelle du nombre de créateurs de contenus sur le forum', year, 'Quantité', 
				seriesCreator
			);
			graphCreator.column();
		
	    	//graph de l'implication des utilisateurs sur forum
	    	var graphImplication=Object.create(Graph); 
	    	var seriesImplication=[{
			        name: '% d\'utilisateurs à avoir rédigé un topic',
			        data: this.tabTauxTopics
			    }, {
			        name: '% d\'utilisateurs à avoir rédigé un message',
			        data: this.tabTauxReactions
			    }];
			graphImplication.init('graphImplication', 'Taux mensuel d\'implication des créateurs sur le forum', year, 'Pourcentage', 
				seriesImplication
			);
			graphImplication.column();
				
	    	//graph nbre topics dans forum
	    	var graphTopicsCategory=Object.create(Graph); 
			graphTopicsCategory.init('graphTopicsCategory', 'Evolution mensuelle du nombre de topics par catégorie', year, 'Quantité', 
				this.tabTopicsByCategories
			);
			graphTopicsCategory.column();

	    	//graph nbre messages dans forum
	    	var graphReactionsCategory=Object.create(Graph); 
			graphReactionsCategory.init('graphReactionsCategory', 'Evolution mensuelle du nombre de messages par catégorie', year, 'Quantité', 
				this.tabReactionsByCategories
			);
			graphReactionsCategory.column();

			//camemberts :			
			//topics
	    	var seriesChartTopics={'nom':'Productivité','donnees':this.topicsCreatorsList};
	    	var chartTopics=Object.create(Graph); 			    	
			chartTopics.init('chartTopics', 'Productivité des créateurs de topics', year, '', 
				seriesChartTopics
			);
			chartTopics.pie(true);

			//reactions
	    	var seriesChartReactions={'nom':'Productivité','donnees':this.reactionsCreatorsList};
	    	var chartReactions=Object.create(Graph); 			    	
			chartReactions.init('chartReactions', 'Productivité des créateurs de messages', year, '', 
				seriesChartReactions
			);
			chartReactions.pie(true);
						
			//likes
	    	var seriesChartLikes={'nom':'Likes','donnees':this.usersLiked};
	    	var chartLikes=Object.create(Graph); 			    	
			chartLikes.init('chartLikes', 'Utilisateurs likés', year, '', 
				seriesChartLikes
			);
			chartLikes.pie(false);

			//signaleurs
	    	var seriesChartSignaleurs={'nom':'Signalements','donnees':this.usersSignaleurs};
	    	var chartSignaleurs=Object.create(Graph); 			    	
			chartSignaleurs.init('chartSignaleurs', 'Nombre de signalements émis par auteur', year, '', 
				seriesChartSignaleurs
			);
			chartSignaleurs.pie(false);
	
			//signalés
	    	var seriesChartSignaled={'nom':'Signalements','donnees':this.usersSignaled};
	    	var chartSignaled=Object.create(Graph); 			    	
			chartSignaled.init('chartSignaled', 'Nombre de signalements reçus par auteur', year, '', 
				seriesChartSignaled
			);
			chartSignaled.pie(false);
      },


    };

	/*
	**********************************************************
	1.2.
	Graph :
	**********************************************************
	 */
  	var Graph={

      init:function(idDiv, titleGraph, year, titleYAxis, series){ 

        this.idDiv=idDiv;
        this.titleGraph=titleGraph;
        this.year='Année '+ year;
        this.titleYAxis=titleYAxis;
        this.series=series;

        this.months=['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'];
        //--------
       },


 	   //les différents graphs
 	   line:function(){

 	   	   var objet=this;

 			Highcharts.chart(objet.idDiv, {
		        chart: {
		            type: 'line'
		        },
		        title: {
		            text: objet.titleGraph
		        },
		        subtitle: {
		            text: objet.year
		        },				        
		        xAxis: {
		            categories: objet.months
		        },
		        yAxis: {
		            title: {
		                text: objet.titleYAxis
		            }
		        },
		        plotOptions: {
		            line: {
		                dataLabels: {
		                    enabled: true
		                },
		                enableMouseTracking: false
		            }
		        },
		        series: objet.series
		    });	   	

 	   },  
 	   
 	   column:function(){ 

 	   		var objet=this;

 	   		var percent='';
 	   		if(objet.titleYAxis=='Pourcentage'){ //unite pour yAxis : Quantité ou Pourcentage ;Percent == % quand % sinon ''
 	   			percent=' %';
 	   		}

			Highcharts.chart(objet.idDiv, {
			    chart: {
			        type: 'column'
			    },
		        title: {
		            text: objet.titleGraph
		        },
		        subtitle: {
		            text: objet.year
		        },	
			    xAxis: {
			        categories: objet.months,
			        crosshair: true
			    },
			    yAxis: {
			        min: 0,
			        title: {
			            text: objet.titleYAxis
			        }
			    },
			    tooltip: {
			    	valueSuffix: percent,
			        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
			        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
			            '<td style="padding:0"><b>{point.y}</b></td></tr>',
			        footerFormat: '</table>',
			        shared: true,
			        useHTML: true
			    },
			    plotOptions: {
			        column: {
			            pointPadding: 0.2,
			            borderWidth: 0
			        }
			    },
			    series: objet.series
			});
 	   },

 	   pie:function(percent){ //boolean

 	   		var objet=this;

 	   		var pointFormatValue='{series.name}: <b>{point.y}</b>';
 	   		var formatValue='<b>{point.name}</b>: {point.y}';
 	   		if(percent){
 	   			pointFormatValue='{series.name}: <b>{point.percentage} %</b>';
 	   			formatValue='<b>{point.name}</b>: {point.percentage} %';
 	   		}

			Highcharts.chart(objet.idDiv, {
			    chart: {
			        plotBackgroundColor: null,
			        plotBorderWidth: null,
			        plotShadow: false,
			        type: 'pie'
			    },
			    title: {
			        text: objet.titleGraph
			    },
		        subtitle: {
		            text: objet.year
		        },					    
			    tooltip: {
			        pointFormat: pointFormatValue
			    },
			    plotOptions: {
			        pie: {
			            allowPointSelect: true,
			            cursor: 'pointer',
			            dataLabels: {
			                enabled: true,
			                format: formatValue,
			                style: {
			                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
			                }
			            }
			        }
			    },
			    series: [{
			        name: objet.series.nom,
			        colorByPoint: true,
			        data: objet.series.donnees
			    }]
			});
 	   },
     };

    /*
    **********************************************************************************************************
    2-Création d'objets et gestion d'events:
    -1. Gestion de la création des graphiques, et de l'écoute de l'input de sélection d'une année
    ***********************************************************************************************************
    */   

    /*
    **********************************************************
    2.1.
    Gestion de la création des graphiques, et de l'écoute de l'input de sélection d'une année
    -création de l'objet HandlerGraph : 
    **********************************************************
    */
	if(document.querySelector('#graphUsers')){


 		var url=window.location.href+'/graph';
 		//on recupère la date dans l'input de l'année, pour le transmettre à ajax qui va le transmettre à php
 		var inputYear=document.querySelector('#graph-year-input');
 		var year=inputYear.value;

 		var buttonYear=document.querySelector('#validation-graph-search');
 		buttonYear.addEventListener('click', function(e){
 			year=inputYear.value;
 			//maj des données et de l'affichage des graphiques après sélection d'une date
 			launchGraphs(url,year);
 		});

 		//lancement au chargement de la page des graphiques
 		launchGraphs(url,year);


 	}
 	//requête ajax à chaque changement d'année validée par un click, et maj des graphiques
	function launchGraphs(url,year){
        $.ajax({
		    url: url,
		    type: 'GET',
		    data : {'year':year} ,
		    success: function(data){ 
		    	//Création de l'objet HandlerGraph : 	
				var grapher=Object.create(HandlerGraph); 
		    	HandlerGraph.init(data);		    			    	
		    },
		    error: function(data) {

		    }
		});
	}     

//FIN CODE -------------------------------------------------
});