package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerTransactionRepository
import javax.inject.Inject

class PostCustomerReviewUseCase @Inject constructor(
    private val customerTransactionRepository: CustomerTransactionRepository
) {
    operator fun invoke(transactionId: Int, rating: Float, comment: String) =
        customerTransactionRepository.postReview(transactionId, rating, comment)
}
