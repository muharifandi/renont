package com.rentone.user.core.di

import com.rentone.user.core.datastore.DataStoreManager
import com.rentone.user.data.repository.*
import com.rentone.user.domain.repository.*
import dagger.Binds
import dagger.Module
import dagger.hilt.InstallIn
import dagger.hilt.components.SingletonComponent
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
abstract class RepositoryModule {

    @Binds
    @Singleton
    abstract fun bindSessionRepository(impl: DataStoreManager): SessionRepository

    @Binds
    @Singleton
    abstract fun bindUserRepository(impl: UserRepositoryImpl): UserRepository

    @Binds
    @Singleton
    abstract fun bindVehicleRepository(impl: VehicleRepositoryImpl): VehicleRepository

    @Binds
    @Singleton
    abstract fun bindNewsRepository(impl: NewsRepositoryImpl): NewsRepository

    @Binds
    @Singleton
    abstract fun bindChatRepository(impl: ChatRepositoryImpl): ChatRepository

    @Binds
    @Singleton
    abstract fun bindAuthRepository(impl: AuthRepositoryImpl): AuthRepository

    @Binds
    @Singleton
    abstract fun bindCustomerAccountRepository(impl: CustomerAccountRepositoryImpl): CustomerAccountRepository

    @Binds
    @Singleton
    abstract fun bindCustomerFinanceRepository(impl: CustomerFinanceRepositoryImpl): CustomerFinanceRepository

    @Binds
    @Singleton
    abstract fun bindCustomerTransactionRepository(impl: CustomerTransactionRepositoryImpl): CustomerTransactionRepository

    @Binds
    @Singleton
    abstract fun bindPartnerProfileRepository(impl: PartnerProfileRepositoryImpl): PartnerProfileRepository

    @Binds
    @Singleton
    abstract fun bindPartnerVehicleRepository(impl: PartnerVehicleRepositoryImpl): PartnerVehicleRepository

    @Binds
    @Singleton
    abstract fun bindPartnerPromotionRepository(impl: PartnerPromotionRepositoryImpl): PartnerPromotionRepository

    @Binds
    @Singleton
    abstract fun bindPartnerTransactionRepository(impl: PartnerTransactionRepositoryImpl): PartnerTransactionRepository

    @Binds
    @Singleton
    abstract fun bindPartnerRewardRepository(impl: PartnerRewardRepositoryImpl): PartnerRewardRepository

    @Binds
    @Singleton
    abstract fun bindLookupRepository(impl: LookupRepositoryImpl): LookupRepository
}
