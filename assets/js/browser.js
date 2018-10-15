const serverBrowser = {
    init: function () {
        const controllers = $('.controller'),
            endpoints = $('.endpoint');

        if (controllers.length !== 0) {
            controllers.each(function () {
                const controller = $(this),
                    controllerName = controller.find('.controller__name'),
                    controllerExpand = controllerName.find('span'),
                    controllerEndpoints = controller.find('.controller__endpoints'),
                    cEndpoints = controllerEndpoints.find('.endpoint');

                controllerName.off().on('click', function () {
                    if (controller.hasClass('expanded')) {
                        controllerEndpoints.slideUp(250, function () {
                            controller.removeClass('expanded');
                        });
                    } else {
                        controllerEndpoints.slideDown(250, function () {
                            controller.addClass('expanded');
                        });
                    }
                });
                controllerExpand.off().on('click', function () {
                    if (controller.hasClass('expanded')) {
                        controllerEndpoints.slideUp(250, function () {
                            controller.removeClass('expanded');

                            cEndpoints.each(function () {
                                const endpoint = $(this),
                                    endpointDetails = endpoint.find('.endpoint__details');

                                endpointDetails.slideUp(250, function () {
                                    endpoint.removeClass('expanded');
                                });
                            });
                        });
                    } else {
                        cEndpoints.each(function () {
                            const endpoint = $(this),
                                endpointDetails = endpoint.find('.endpoint__details');

                            endpointDetails.slideDown(0, function () {
                                endpoint.addClass('expanded');
                            });
                        });

                        controllerEndpoints.slideDown(250, function () {
                            controller.addClass('expanded');
                        });
                    }
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
    }
};

$(document).ready(function () {
    console.log('aaa');
    serverBrowser.init();
});
