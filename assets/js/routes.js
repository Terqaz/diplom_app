export const ROUTES = {
    app_survey_show: (id) => `/survey/${id}`,
    app_survey_form_edit: (id) => `/survey/${id}/form/edit`,
    app_survey_schedule_edit: (id) => `/survey/${id}/schedule/edit`,

    app_bot_show: (id) => `/bot/${id}`,
    app_bot_connections_edit: (id) => `/bot/${id}/connections/edit`,

    app_connection_test: (code) => `/connection/${code}/test`,

    app_bot_user_access_change: (id, action) => `/bot/${id}/user-access/${action}`,
    app_survey_user_access_change: (id, action) => `/survey/${id}/user-access/${action}`,
};
