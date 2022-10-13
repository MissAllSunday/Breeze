import { NoticeOptions, ToastCallback } from 'breezeTypes'
import toast from 'react-hot-toast'

const setNotice = (options: NoticeOptions, onCloseCallback: ToastCallback) => {
  toast.custom("<div class='infobox'>Hello World</div>")
}

const clearNotice = () => {
  toast.dismiss()
}

export default { setNotice, clearNotice }
