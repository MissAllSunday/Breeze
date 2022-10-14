import { NoticeOptions, ToastCallback } from 'breezeTypes'
import toast from 'react-hot-toast'

const setNotice = (options: NoticeOptions, onCloseCallback: ToastCallback): void => {
  toast.custom("<div class='infobox'>Hello World</div>")
}

const clearNotice = (): void => {
  toast.dismiss()
}

export default { setNotice, clearNotice }
