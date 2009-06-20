
var storagePcContainerIdBase = "storagePcContainer";

function loadStorageBox(obj, targetBoxIndex, targetBoxNum)
{
	// Quickview aktualisieren
	$$(".boxInterface")[targetBoxIndex].select(".quickList ul li a").invoke('removeClassName', 'selected');
	$$(".boxInterface")[targetBoxIndex].select(".quickList ul li a")[targetBoxNum].addClassName("selected");
	ajaxLoad(obj.href, 'get', 
		{ajax :1},
		"ajaxLoadedContent",
		function() {
			removeWhiteSpaces(storagePcContainerIdBase + targetBoxIndex);
			completeStorageBoxLoading(targetBoxIndex);
		}
	);
}

function completeStorageBoxLoading(boxIndex)
{
	var targetBox = $$(".boxInterface")[boxIndex].select(".boxContent")[0];
	var ajaxLoaded = $("ajaxLoadedContent").innerHTML;
	$("ajaxLoadedContent").innerHTML = "";
	targetBox.replace(ajaxLoaded);
	initializeStoragePc(storagePcContainerIdBase + boxIndex);
}

function initializeAllStoragePcs()
{
	var i = 0;
	for (; $(storagePcContainerIdBase + i); i++) {
		initializeStoragePc(storagePcContainerIdBase + i);
	}
}

function initializeStoragePc(pcContainer) {
	// Box droppable machen
	Droppables.add(pcContainer, {
		accept :"pokemon",
		onHover : function(elDrag, elDrop, perc) {
			elDrag.setStyle( {
				cursor :'pointer'
			});
		},
		onDrop : function(elDrag, elDrop, ev) {
			elDrop.setStyle( {
				cursor :'auto'
			});
			new Effect.Highlight(elDrop);
			var teamSlot = $(elDrag.id);
			var emptySlot = $$("#" + elDrop.id + " .emptySlot")[0];
			// Pokemon zu Box hinzufügen
		emptySlot.innerHTML = elDrag.innerHTML;
		emptySlot.removeClassName("emptySlot");
		emptySlot.addClassName("pokemon");
		emptySlot.addClassName("boxPokemon");
		emptySlot.dragHandle = new Draggable(emptySlot.id, {
			revert :true,
			zindex :2000
		});
		emptySlot.setStyle( {
			cursor :'move'
		});
		// Und aus Team entfernen
		if (!teamSlot.hasClassName("boxPokemon")) {
			teamSlot.innerHTML = "---";
		} else {
			teamSlot.innerHTML = "&nbsp;";
			teamSlot.removeClassName("boxPokemon");
		}
		teamSlot.removeClassName("pokemon");
		teamSlot.addClassName("emptySlot");
		teamSlot.dragHandle.destroy();
		teamSlot.setStyle( {
			cursor :'auto'
		});
	}
	});

	// Pokemonelemente dragbar machen
	var draggableObjects = $$("#" + pcContainer + " .pokemon");
	draggableObjects.each( function(item) {
		item.dragHandle = new Draggable(item.id, {
			revert :true,
			zindex :2000
		});
		item.setStyle( {
			cursor :'move'
		});
	});

	// Sortable.create(pcContainer, {constraint: 0});
	$(pcContainer).setStyle( {
		position :'static'
	});
}

function initializeStoragePcPokemonTeam() {
	var teamId = "pokemonTeamListing";
	// Team droppable machen
	Droppables.add(teamId, {
		accept :"pokemon",
		onHover : function(elDrag, elDrop, perc) {
			elDrag.setStyle( {
				cursor :'pointer'
			});
		},
		onDrop : function(elDrag, elDrop, ev) {
			elDrop.setStyle( {
				cursor :'auto'
			});
			new Effect.Highlight(elDrop);
			var boxPokemon = elDrag;
			var emptySlot = $$("#" + elDrop.id + " .emptySlot")[0];
			// Pokemon zu Team kopieren
		emptySlot.innerHTML = boxPokemon.innerHTML;
		emptySlot.removeClassName("emptySlot");
		emptySlot.removeClassName("boxPokemon");
		emptySlot.addClassName("pokemon");
		emptySlot.dragHandle = new Draggable(emptySlot.id, {
			revert :true,
			zindex :2000
		});
		emptySlot.setStyle( {
			cursor :'move'
		});
		// Und aus Box entfernen
		boxPokemon.innerHTML = "&nbsp;";
		boxPokemon.removeClassName("pokemon");
		boxPokemon.removeClassName("boxPokemon");
		boxPokemon.addClassName("emptySlot");
		boxPokemon.dragHandle.destroy();
		boxPokemon.setStyle( {
			cursor :'auto'
		});
	}
	});

	// Pokemonelemente dragbar machen
	var draggableObjects = $$("#" + teamId + " .pokemon");
	draggableObjects.each( function(item) {
		item.dragHandle = new Draggable(item.id, {
			revert :true,
			zindex :2000
		});
		item.setStyle( {
			cursor :'move'
		});
	});

	// Sortable.create(teamId, {constraint: 0});
	$(teamId).setStyle( {
		position :'static',
		zIndex: 10000
	});
	$$(".sideBoxInner")[0].setStyle({overflow:'visible'});
}