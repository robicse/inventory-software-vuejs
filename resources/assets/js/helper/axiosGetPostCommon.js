import axios from "axios";

const {format} = require('number-currency-format');

export default {
    data() {
        return {
            inputFields: [],
            hidePreLoader: true,
            isActive: false,
            selectedItemId: '',
            deleteID: '',
            deleteIndex: '',
            updateIndex: '',
            accounting: '',
            isShortcutAbleKey:false,

        }
    },
    mounted() {

        let instance = this;

        this.$hub.$on('selectedDeletableId', function (id, index) {
            instance.deleteID = id;
            instance.deleteIndex = index;
        });
    },
    methods: {
        axiosGet(url, onSuccess, onError) {
            axios.get(window.appConfig.appUrl + url)
                .then(function (response) {
                    if (onSuccess) onSuccess(response);

                }.bind(this))
                .catch(function (error) {
                    if (onError) onError(error);
                })
        },
        axiosPost(url, postData, onSuccess, onError) {
            axios.post(window.appConfig.appUrl+url, postData)
                .then(function (response) {
                    if (onSuccess) onSuccess(response);
                    this.showSuccessAlert(response.data.message);

                }.bind(this))
                .catch(({response}) => {
                    if (onError) onError(response);
                    this.showErrorAlert(response.data.message);

                });
        },
        loginAxiosPost(url, postData, onSuccess, onError) {
            axios.post(window.appConfig.appUrl+url, postData)
                .then(function (response) {
                    if (onSuccess) onSuccess(response);

                }.bind(this))
                .catch(({response}) => {
                    if (onError) onError(response);
                });
        },
        postDataMethod(route, fields) {
            let instance = this;
            instance.setPreLoader(false);
            instance.axiosPost(route, fields,
                function (response) {
                    instance.setPreLoader(true);
                    instance.postDataThenFunctionality(response);
                },
                function (error) {
                    instance.setPreLoader(true);
                    instance.postDataCatchFunctionality(error);

                });
        },

        loginPostMethod(route, fields) {
            let instance = this;
            instance.setPreLoader(false);
            instance.loginAxiosPost(route, fields,
                function (response) {
                    instance.setPreLoader(true);
                    instance.loginPostSucces(response);
                },
                function (error) {
                    instance.setPreLoader(true);
                    instance.loginPostError(error);

                });
        },
        deleteDataMethod(deleteDataURL, deleteIndex) {
            let instance = this;
            $('#confirm-delete').modal('hide');
            this.deleteActionPreLoader(false);
            axios.post(window.appConfig.appUrl + deleteDataURL, {})
                .then(function (response) {
                    instance.deleteActionPreLoader(true);
                    instance.showSuccessAlert(response.data.message);
                    instance.$hub.$emit('reloadDataTable');
                })
                .catch(({response}) => {
                    instance.showErrorAlert(response.data.message);
                    instance.deleteActionPreLoader(true);
                    instance.showErrorAlert(response.data.message);

                });
        },
        deleteActionPreLoader(value) {
            this.$hub.$emit("deleteActionPreLoader", value);
        },
        redirect(route) {
            location.href = window.appConfig.appUrl + route;
        },
        axiosGETorPOST(options, callback) {
            let axiosCall;

            if (options.postData) //if post method
            {
                axiosCall = axios.post(window.appConfig.appUrl+options.url, options.postData)
            }
            else //else get method
            {
                axiosCall = axios.get(window.appConfig.appUrl+options.url)
            }
            axiosCall.then(response => {
                if (callback) callback(true, response.data); //returns response data

            }).catch(({response}) => {
                if (callback) callback(false, response.data); //returns error response data
            });
        },
        modalCloseAction(modalID) {
            let instance = this;
            $(modalID).on('hidden.bs.modal', function (e) {
                instance.isActive = false;
            });

        },
        /*toaster success alert Massage*/
        showSuccessAlert(message) {
            this.$toasted.global.success({
                message: message
            });
        },
        /*toaster error alert Massage*/
        showErrorAlert(message) {
            this.$toasted.global.error({
                message: message
            });
        },
        setPreLoader(value) {
            let instance = this;
            instance.hidePreLoader = value;
        },
        addEditAction(id) {
            this.selectedItemId = id;
            this.isActive = true;
        },
        //Excel Files data converted into json objects
        ExcelToJSON(file, callback) {
            let reader = new FileReader(),
                result;
            reader.onload = function (e) {
                let data = e.target.result;
                let workbook = XLSX.read(data, {
                    type: 'binary'
                });
                workbook.SheetNames.forEach(function (sheetName) {
                    let XL_row_object = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], {raw: true, defval: null});
                    result = JSON.stringify(XL_row_object);
                });
                let result = JSON.parse("[" + result + "]");
                let parsedData = [];
                //removing unwanted empty cells after the data from excel
                _.forEach(result, function (value) {
                    _.forIn(value, function (value, key) {
                        if (_.compact(Object.values(value)).length == 0) {
                            parsedData.push(key);
                        }
                    });
                });
                for (let i = 0; i < result.length; i++) {
                    result[0].splice(parsedData[i], parsedData.length);
                }
                if (callback && result) callback(result[0]);
            };
            reader.onerror = function (error) {
            };
            reader.readAsBinaryString(file[0]);
        },
        numberFormat(number) {
                let position, spacing = false, thousandSeparator, decimalSeparator = false;
                if (this.currencyPosition === 'left') {
                    position = 'LEFT';

                } else if (this.currencyPosition === 'right') {
                    position = "RIGHT";

                } else if (this.currencyPosition === 'left-space') {
                    position = "LEFT";
                    spacing = true;
                } else {
                    position = "RIGHT";
                    spacing = true;
                }
                if (this.thousandSeparator === "space") {
                    thousandSeparator = " "
                } else {
                    thousandSeparator = this.thousandSeparator;
                }
                if (this.numDec == 0) {
                    decimalSeparator = "";
                } else {
                    decimalSeparator = this.decimalSeparator;
                }
                return format(number, {
                    currency: this.currencySymbol,
                    spacing: spacing,
                    currencyPosition: position,
                    thousandSeparator: thousandSeparator,
                    decimalSeparator: decimalSeparator,
                    decimalsDigits: this.numDec,
                });
        },
        percentFormatting(number){

            let data, spacing = false, thousandSeparator, decimalSeparator = false;
            if (this.thousandSeparator === "space") {
                thousandSeparator = " "
            } else {
                thousandSeparator = this.thousandSeparator;
            }
            if (this.numDec == 0) {
                decimalSeparator = "";
            } else {
                decimalSeparator = this.decimalSeparator;
            }
            data =  format(number, {
                spacing: spacing,
                thousandSeparator: thousandSeparator,
                decimalSeparator: decimalSeparator,
                decimalsDigits: this.numDec,
            });

            return data+'%';
        },
        numberFormatting(number) {

            let position,spacing = false,thousandSeparator,decimalSeparator = false;

            if (this.thousandSeparator === "space") {
                thousandSeparator = " "
            } else {
                thousandSeparator = this.thousandSeparator;
            }
            return format(number,{
                thousandSeparator: thousandSeparator,
            });
        },
        zeroFormatForOpening(number){
            let position, spacing = false, thousandSeparator, decimalSeparator = false;
            if(this.currencyPosition === 'left') {
                position = 'LEFT';
            }else if (this.currencyPosition === 'right') {
                position = "RIGHT";
            }else if (this.currencyPosition === 'left-space') {
                position = "LEFT";
                spacing = true;
            }else {
                position = "RIGHT";
                spacing = true;
            }
            if(this.thousandSeparator === "space") {
                thousandSeparator = " "
            }else{
                thousandSeparator = this.thousandSeparator;
            }
            if(this.numDec == 0) {
                decimalSeparator = "";
            }else {
                decimalSeparator = this.decimalSeparator;
            }
            return format(number, {
                currency: this.currencySymbol,
                spacing: spacing,
                currencyPosition: position,
                thousandSeparator: thousandSeparator,
                decimalSeparator: decimalSeparator,
                decimalsDigits: this.numDec,
            });
        },
        zeroFormat(number, check){
            if(number == "" && check == null){
                return null;
            }
            else{

                let position, spacing = false, thousandSeparator, decimalSeparator = false;
                if(this.currencyPosition === 'left') {
                    position = 'LEFT';
                }else if (this.currencyPosition === 'right') {
                    position = "RIGHT";
                }else if (this.currencyPosition === 'left-space') {
                    position = "LEFT";
                    spacing = true;
                }else {
                    position = "RIGHT";
                    spacing = true;
                }
                if(this.thousandSeparator === "space") {
                    thousandSeparator = " "
                }else{
                    thousandSeparator = this.thousandSeparator;
                }
                if(this.numDec == 0) {
                    decimalSeparator = "";
                }else {
                    decimalSeparator = this.decimalSeparator;
                }
                return format(number, {
                   currency: this.currencySymbol,
                    spacing: spacing,
                    currencyPosition: position,
                    thousandSeparator: thousandSeparator,
                    decimalSeparator: decimalSeparator,
                    decimalsDigits: this.numDec,
                });
            }
        },
        dateFormats(dt){
            return moment(dt).format(this.dateFormat);
        },
        dateFormatsWithTime(time){
            if (this.timeFormat == '12h') {
                return moment(time).format("hh:mm A");
            }else{
                return moment(time).format("HH:mm:ss");
            }
        },
        keyboardAscciValueReader(id){
            let instance = this;
           $(id).on("keydown",function (event){
                if(event.which == 16){
                    instance.isShortcutAbleKey = true;
                }
                //noinspection JSAnnotator
                else if (event.which == 17){
                    $(id).val('ctrl+');
                }
                else if (event.which == 18){
                    instance.isShortcutAbleKey = true;
                }else if (event.which == 13){
                    instance.isShortcutAbleKey = true;
                }else{
                    instance.isShortcutAbleKey = false;
                }
            });
            return instance.isShortcutAbleKey;
        },
        globalShortcutMethod(){
            let instance = this;
            if(instance.shortcutStatus == 1){
                instance.redirect('/sales');
            }
        },
        find_duplicate_in_array(arra1) {
            let object = {},
                result = [];
            arra1.forEach(function (item){
                if(!object[item])
                    object[item] = 0;
                object[item] += 1;
            })
            for (let prop in object) {
                if(object[prop] >= 2) {
                    result.push(prop);
                }
            }
            return result;
        },
        shortCutKeyConversion(){
            if(this.shortcutKeyInfo.loadSalesPage.status == 1){
                if (this.shortcutKeyInfo.loadSalesPage.shortCutKey.includes("+")) return this.shortcutKeyInfo.loadSalesPage.shortCutKey.split("+");
                else return [this.shortcutKeyInfo.loadSalesPage.shortCutKey];
            }
        },

        PrintElem(elem)
        {
            var mywindow = window.open('', 'PRINT', 'height=400,width=600');
            mywindow.document.write('<html><head><title>' + document.title  + '</title>');
            mywindow.document.write('</head><body >');
            mywindow.document.write('<h1>' + document.title  + '</h1>');
            mywindow.document.write(document.getElementById(elem).innerHTML);
            mywindow.document.write('</body></html>');
            mywindow.document.close(); // necessary for IE >= 10
            mywindow.focus(); // necessary for IE >= 10*/
            mywindow.print();
            mywindow.close();
            return true;
        },
        decimalFormat(number) {
            if(number != null ){
                return number.toString().replace(/[.]/, this.decimalSeparator);
            }
        }
    }

};
