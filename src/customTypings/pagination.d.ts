declare module 'breezeTypes' {
  interface PaginationProps {
    limit: number
    total: number
    onChange: function
  }
}

module.exports = {
  PaginationProps
}
