(function ()
{
	"use strict";
	BX.namespace("BX.Report.VisualConstructor.Board");

	BX.Report.VisualConstructor.Board.Controls = function (options)
	{
		this.addFormSlider = null;
		this.reportCategories = options.reportCategories;
		this.configurationButton = options.configurationButton;
		this.boardId = options.boardId;
		this.demoToggle = options.demoToggle;

		this.init();
	};

	BX.Report.VisualConstructor.Board.Controls.prototype = {
		init: function ()
		{
			BX.bind(
				BX('add_report_popup_button'),
				'click',
				this.handlePopupButtonClick.bind(this)
			);

			BX.bind(
				this.configurationButton,
				'click',
				this.handleConfigurationButtonClick.bind(this)
			);
			BX.addCustomEvent("BX.Report.VisualConstructor.afterWidgetAdd", this.closeSlider.bind(this));
		},
		handleConfigurationButtonClick: function()
		{
			if (this.confirmationPopup)
			{
				return;
			}

			this.confirmationPopup = new BX.Main.Menu({
				id: 'visualconstructor-dashboard-configuration-popup',
				bindElement: this.configurationButton,
				closeByEsc: true,
				autoHide: true,
				cacheable: false,
				items: [
					{
						text: this.getToggleBoardTitle(),
						onclick: this.toggleBoard.bind(this),
					},
					{
						text: this.getDemoModeToggleButtonTitle(),
						onclick: this.toggleDemoMode.bind(this),
					},
				],
				events: {
					onPopupClose: () => {
						this.confirmationPopup = null;
					},
				},
			});

			this.confirmationPopup.show();
		},

		handlePopupButtonClick: function ()
		{
			this.openSlider();
		},
		openSlider: function ()
		{
			this.addFormSlider = BX.SidePanel.Instance;
			this.addFormSlider.open("widget:add-widget-to-board", {
				cacheable: false,
				contentCallback: BX.delegate(function getSliderContent(slider) {
					var promise = new BX.Promise();
					BX.Report.VC.Core.ajaxGet('board.showAddForm', {
						urlParams: {
							'categories': this.reportCategories,
							'boardId': this.boardId
						},
						onFullSuccess: BX.delegate(function (result)
						{
							slider.getData().set("addFormContent", result.data);
							promise.fulfill(result.data);
							var contentContainer = slider.getContentContainer();
							contentContainer.addEventListener('scroll', function() {
								BX.Report.VC.PopupWindowManager.adjustPopupsPositions();
							});
						}, this)
					});
					return promise;
				}, this),
				animationDuration: 100,
				width: 950,
				events: {
					onLoad: function(event) {
						var slider = event.getSlider();
						BX.html(null, slider.getData().get("addFormContent"));
					},
					onClose: function()
					{
						BX.Report.VC.PopupWindowManager.closeAllPopups()
					}
				}
			});
		},
		closeSlider: function()
		{
			this.addFormSlider.closeAll();
		},
		getToggleBoardTitle: function()
		{
			return BX.message('VISUALCONSTRUCTOR_DASHBOARD_GO_TO_DEFAULT');
		},
		getDesignerModeToggleButtonTitle: function()
		{
			var board = BX.VisualConstructor.BoardRepository.getBoard(this.boardId).getDashboard();
			return !board.isDesignerMode() ? BX.message('VISUALCONSTRUCTOR_DASHBOARD_DESIGN_MODE_ON_TITLE') : BX.message('VISUALCONSTRUCTOR_DASHBOARD_DESIGN_MODE_OFF_TITLE')
		},
		getDemoModeToggleButtonTitle: function()
		{
			var board = BX.VisualConstructor.BoardRepository.getBoard(this.boardId);
			return !board.isDemoMode() ? BX.message('VISUALCONSTRUCTOR_DASHBOARD_DEMO_MODE_ON_TITLE') : BX.message('VISUALCONSTRUCTOR_DASHBOARD_DEMO_MODE_OFF_TITLE')
		},
		toggleBoard: function ()
		{
			BX.Report.VC.Core.abortAllRunningRequests();
			BX.Report.VC.Core.ajaxPost('board.toggleToDefault', {
				data: {
					boardKey: this.boardId
				},
				onFullSuccess: BX.delegate(function (response) {
					var board = BX.VisualConstructor.BoardRepository.getBoard(this.boardId);
					if (!response.errors.length)
					{
						board.getDashboard().clearRows();
						board.getDashboard().destroy();

						BX.Report.VC.Core.ajaxGet('widget.loadByBoardId', {
							urlParams: {
								'boardId': this.boardId
							},
							onFullSuccess: BX.delegate(function (result) {
								if (result.data.rows)
								{
									board.getDashboard().addRows(result.data.rows);
									board.getDashboard().render();
								}
							}, this)
						});
					}
				}, this)
			});
			this.confirmationPopup.close();
		},
		toggleBoardDesignerMode: function()
		{
			var board = BX.VisualConstructor.BoardRepository.getBoard(this.boardId).getDashboard();
			board.toggleDesignerMode();
			this.confirmationPopup.close();
		},
		toggleDemoMode: function()
		{
			var board = BX.VisualConstructor.BoardRepository.getBoard(this.boardId);
			board.toggleDemoMode();
			this.confirmationPopup.close();
		}
	}

})();
