/**
 * Domain checker object contains methods to perform requests to upload files, check files progress and set progress
 * dynamically to show user actual view.
 */
let DomainChecker = {

    /**
     * Valid csv file type.
     */
    CSV_FILE_TYPE: 'application/vnd.ms-excel',

    /**
     * Indicates that poll progress already started.
     */
    POLL_STARTED: false,

    /**
     * Initializes functions.
     */
    init: function () {
        this.handleSubmitForm();
        this.pollProgress();
    },

    /**
     * Handle submitted form.
     *
     * Collect files to send to create files queue action.
     * Appends created files to results container and starts poll progress if it is not started yet.
     */
    handleSubmitForm: function () {
        let form = document.getElementById('uploadFilesForm');

        if (!form) {
            return;
        }

        let action = form.getAttribute('action');

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            let formData = this.getFilesFormData(form);

            if (!formData) {
                this.showError('Please, upload files!');
                return false;
            }

            this.createFilesQueue(action, formData).then(function (response) {
                if (!response.success) {
                    this.showError('Something went wrong!!!');
                    return;
                }

                this.appendDomainFiles(response.domainFiles);

                if (!this.POLL_STARTED) {
                    this.pollProgress();
                }
            }.bind(this), function (message) {
                this.showError(message)
            }.bind(this));

            return false;
        }.bind(this));
    },

    /**
     * Performs create files queue request.
     *
     * @param action
     * @param formData
     *
     * @return {Promise<unknown>}
     */
    createFilesQueue: function (action, formData) {
        return this.request(action, formData, 'POST');
    },

    /**
     * Appends domain files to results container.
     *
     * @param domainFiles
     */
    appendDomainFiles: function (domainFiles) {
        let resultsContainer = document.getElementById('resultsContainer');
        let resultItemForClone = document.getElementById('resultItemForClone');
        let resultItemClone;

        domainFiles.forEach(function (domainFile) {
            resultItemClone = resultItemForClone.cloneNode(true);

            resultItemClone.removeAttribute('id');
            resultItemClone.classList.remove('is--hidden');
            resultItemClone.classList.add('f_result-item');

            resultItemClone.setAttribute('data-id', domainFile.id);
            resultItemClone.querySelector('.f_title').innerText = domainFile.original_name;
            resultItemClone.querySelector('.f_result-link').setAttribute('href', '/result/' + domainFile.id);

            resultsContainer.appendChild(resultItemClone);
        });

        resultsContainer.classList.remove('is--hidden');
    },

    /**
     * Polls progress in case if there is files which status should be checked and
     * poll is not started yet.
     */
    pollProgress: function () {
        let resultItems = document.querySelectorAll('.f_result-item');

        if (!resultItems.length) {
            this.POLL_STARTED = false
            return;
        }

        this.POLL_STARTED = true;

        this.fetchDomainFilesProgress().then(function (response) {
            if (!response.filesProgress) {
                this.showError('No files progress to show');
                return;
            }

            this.setDomainFilesProgress(response.filesProgress);

            setTimeout(this.pollProgress.bind(this), 3000);
        }.bind(this), function (message) {
            this.showError(message)
        }.bind(this));
    },

    /**
     * Performs fetch files progress request.
     *
     * @return {Promise<unknown>}
     */
    fetchDomainFilesProgress: function () {
        return this.request('/queue-progress');
    },

    /**
     * Performs request to set domain file as completed.
     *
     * @param id
     *
     * @return {Promise<unknown>}
     */
    setDomainFileCompletedStatus: function (id) {
        let formData = new FormData();

        formData.append('id', id);

        return this.request('/set-completed-status', formData, 'POST');
    },

    /**
     * Set domain files progress.
     * In case if domain file check is completed calls setDomainFileCompletedStatus().
     *
     * @param filesProgress
     */
    setDomainFilesProgress: function (filesProgress) {
        let resultItem;
        let completedResultItem;

        filesProgress.forEach(function (fileProgress) {
            resultItem = document.querySelector('.f_result-item[data-id="' + fileProgress.fileId + '"]');

            resultItem.querySelector('.f_progress-runner').style.width = fileProgress.progress + '%';
            resultItem.querySelector('.f_progress-percent').innerHTML = fileProgress.progress + '%';

            if (fileProgress.progress === 100) {
                this.setDomainFileCompletedStatus(fileProgress.fileId).then(function (response) {
                    if (!response.success) {
                        this.showError('Something went wrong!!!');
                        return;
                    }

                    completedResultItem = document.querySelector('.f_result-item[data-id="' + response.fileId + '"]');

                    completedResultItem.querySelector('.f_action-cell').classList.remove('is--hidden');
                    completedResultItem.classList.remove('f_result-item');
                }.bind(this), function (message) {
                    this.showError(message)
                }.bind(this));
            }
        }.bind(this));
    },

    /**
     * Returns form data from given form files.
     *
     * @param form
     *
     * @return {boolean|FormData}
     */
    getFilesFormData: function (form) {
        let filesInput = form.querySelector('.f_files-input');
        let files = this.getValidFiles(filesInput.files);

        if (!files.length) {
            return false;
        }

        let formData = new FormData();

        for (let index = 0; index < files.length; ++index) {
            formData.append('files[]', files[index]);
        }

        return formData;
    },

    /**
     * Returns valid csv files from given files.
     *
     * @param files
     * @return {[]}
     */
    getValidFiles: function (files) {
        let validFiles = [];

        for (let index = 0; index < files.length; ++index) {
            if (!files[index].type === this.CSV_FILE_TYPE) {
                continue;
            }

            validFiles.push(files[index]);
        }

        return validFiles;
    },

    /**
     * Alerts and logs error message.
     *
     * @param message
     */
    showError: function (message) {
        alert(message);
        console.error(message);
    },

    /**
     * Performs request by given params.
     *
     * @param action
     * @param formData
     * @param method
     *
     * @return {Promise<unknown>}
     */
    request: function (action, formData = null, method = 'GET') {
        let params = {};

        params.method = method;
        if (formData) {
            params.body = formData;
        }

        return new Promise(function (resolve, reject) {
            fetch(action, params).then(function (response) {
                if (response.ok) {
                    response.json().then(function (result) {
                        resolve(result);
                    });

                    return;
                }

                reject('Something went wrong!!!');
            });
        });
    }

};

// initial call
document.addEventListener('DOMContentLoaded', function () {
    DomainChecker.init();
});