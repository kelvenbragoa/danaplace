import toastr from 'toastr';



    

export function useToastr() {

    toastr.options.closeButton = true;
    toastr.options.progressBar = true;
    // toastr.options.positionClass = 'toast-bottom-right';

    return toastr;
}