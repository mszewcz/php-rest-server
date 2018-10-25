const serverBrowser = {
    init: function () {
        const t = this,
            controllers = $('.controller'),
            endpoints = $('.endpoint');

        if (controllers.length !== 0) {
            controllers.each(function () {
                const controller = $(this),
                    controllerHeading = controller.find('.controller__heading'),
                    controllerName = controllerHeading.find('span.name'),
                    controllerShowE = controllerHeading.find('span.exp-c'),
                    controllerHideE = controllerHeading.find('span.col-c'),
                    controllerShowD = controllerHeading.find('span.exp-e'),
                    controllerHideD = controllerHeading.find('span.col-e'),
                    controllerEndpoints = controller.find('.controller__endpoints'),
                    cEndpoints = controllerEndpoints.find('.endpoint');

                controllerName.off().on('click', function () {
                    if (controller.hasClass('expanded')) {
                        t.collapseController(controller, controllerEndpoints, cEndpoints);
                    } else {
                        t.expandController(controller, controllerEndpoints);
                    }
                });
                controllerShowE.off().on('click', function () {
                    t.expandController(controller, controllerEndpoints);
                });
                controllerHideE.off().on('click', function () {
                    t.collapseController(controller, controllerEndpoints, cEndpoints);
                });
                controllerShowD.off().on('click', function () {
                    const duration = controller.hasClass('expanded') ? 250 : 0;

                    t.expandEndpoints(cEndpoints, duration);
                    controller.addClass('ep-expanded');

                    if (!controller.hasClass('expanded')) {
                        controllerEndpoints.slideDown(250, function () {
                            controller.addClass('expanded');
                        });
                    }
                });
                controllerHideD.off().on('click', function () {
                    t.collapseEndpoints(cEndpoints, 250);
                    controller.removeClass('ep-expanded');
                });
            });
        }
        if (endpoints.length !== 0) {
            endpoints.each(function () {
                const endpoint = $(this),
                    endpointHeading = endpoint.find('.endpoint__heading'),
                    endpointDetails = endpoint.find('.endpoint__details');

                endpointHeading.off().on('click', function () {
                    if (endpoint.hasClass('expanded')) {
                        endpointDetails.slideUp(250, function () {
                            endpoint.removeClass('expanded');
                        });
                    } else {
                        endpointDetails.slideDown(250, function () {
                            endpoint.addClass('expanded');
                        });
                    }
                });
            });
        }
    },
    expandController: function (controller, controllerEndpoints) {
        controllerEndpoints.slideDown(250, function () {
            controller.addClass('expanded');
        });
    },
    collapseController: function (controller, controllerEndpoints, cEndpoints) {
        const t = this;

        controllerEndpoints.slideUp(250, function () {
            controller.removeClass('expanded');
            t.collapseEndpoints(cEndpoints, 0);
            controller.removeClass('ep-expanded');
        });
    },
    expandEndpoints: function (cEndpoints, duration) {
        cEndpoints.each(function () {
            const endpoint = $(this),
                endpointDetails = endpoint.find('.endpoint__details');

            endpointDetails.slideDown(duration, function () {
                endpoint.removeClass('expanded');
            });
        });
    },
    collapseEndpoints: function (cEndpoints, duration) {
        cEndpoints.each(function () {
            const endpoint = $(this),
                endpointDetails = endpoint.find('.endpoint__details');

            endpointDetails.slideUp(duration, function () {
                endpoint.removeClass('expanded');
            });
        });
    }
};

$(document).ready(function () {
    serverBrowser.init();
});
