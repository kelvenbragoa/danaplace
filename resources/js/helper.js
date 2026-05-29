import moment from 'moment';

export default function formateDate(value){
    if(value){
        return moment(String(value)).format('DD-MM-YYYY');
    }
}