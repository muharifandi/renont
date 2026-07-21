package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerTransactionRepository
import javax.inject.Inject

class PostCustomerReviewUseCase @Inject constructor(
    private val customerTransactionRepository: CustomerTransactionRepository
) {
    operator fun invoke(transactionId: Int, rating: Float, comment: String) =
        customerTransactionRepository.postReview(transactionId, rating, comment)
}
