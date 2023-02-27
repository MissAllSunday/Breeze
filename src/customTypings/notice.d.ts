declare module 'breezeTypes' {

  interface noticeProps {
    options: {
      type: string
      header: string
      body: string
    }
    show: boolean
  }
}

module.exports = {
  noticeProps
}
