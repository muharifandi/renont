package com.nusatim.sapiriku.core.di

import android.content.Context
import android.content.pm.PackageManager
import coil.ImageLoader
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.core.database.dao.UserDao
import com.nusatim.sapiriku.api.service.*
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.android.qualifiers.ApplicationContext
import dagger.hilt.components.SingletonComponent
import kotlinx.coroutines.flow.firstOrNull
import kotlinx.coroutines.runBlocking
import kotlinx.serialization.json.Json
import okhttp3.Interceptor
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import com.jakewharton.retrofit2.converter.kotlinx.serialization.asConverterFactory
import com.nusatim.sapiriku.BuildConfig
import java.util.concurrent.TimeUnit
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object NetworkModule {

    @Provides
    @Singleton
    fun provideJson(): Json = Json {
        ignoreUnknownKeys = true
        coerceInputValues = true
    }

    @Provides
    @Singleton
    fun provideLoggingInterceptor(): HttpLoggingInterceptor {
        return HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        }
    }

    // Skema alternatif diminta user: SECRET_KEY tidak lagi dibaca dari BuildConfig,
    // melainkan dari <meta-data> di AndroidManifest (via resValue, lihat build.gradle.kts).
    // CATATAN AUDIT: ini bukan perbaikan keamanan - hanya memindahkan lokasi nilai yang
    // sama-sama plaintext di dalam APK jadi, dari classes.dex ke resources.arsc /
    // AndroidManifest biner. Lihat BUKTI_RESVALUE_METADATA.txt untuk pembuktian.
    @Provides
    @Singleton
    fun provideAuthInterceptor(
        @ApplicationContext context: Context,
        userDao: UserDao
    ): Interceptor {
        val appInfo = context.packageManager.getApplicationInfo(
            context.packageName,
            PackageManager.GET_META_DATA
        )
        val algoritm: String = appInfo.metaData.getString("ALGORITM").orEmpty()
        val secretKey: String = appInfo.metaData.getString("SECRET_KEY").orEmpty()

        return Interceptor { chain ->
            val user = runBlocking { userDao.getUser().firstOrNull() }
            val request = chain.request().newBuilder()
                .header("User-Agent", BuildConfig.APPLICATION_ID)
                .header("X-App-Secret", secretKey)
                .header("X-App-Secret-Algorithm", algoritm)
                .apply {
                    user?.key?.let { header("key", it) }
                }
                .build()
            chain.proceed(request)
        }
    }

    @Provides
    @Singleton
    fun provideOkHttpClient(
        loggingInterceptor: HttpLoggingInterceptor,
        authInterceptor: Interceptor
    ): OkHttpClient {
        return OkHttpClient.Builder()
            .addInterceptor(loggingInterceptor)
            .addInterceptor(authInterceptor)
            .connectTimeout(1, TimeUnit.MINUTES)
            .readTimeout(1, TimeUnit.MINUTES)
            .build()
    }

    @Provides
    @Singleton
    fun provideRetrofit(okHttpClient: OkHttpClient, json: Json): Retrofit {
        return Retrofit.Builder()
            .baseUrl(Config.API_URL)
            .client(okHttpClient)
            .addConverterFactory(json.asConverterFactory("application/json".toMediaType()))
            .build()
    }

    @Provides
    @Singleton
    fun provideBasicService(retrofit: Retrofit): BasicService = retrofit.create(BasicService::class.java)

    @Provides
    @Singleton
    fun provideCustomerService(retrofit: Retrofit): CustomerService = retrofit.create(CustomerService::class.java)

    @Provides
    @Singleton
    fun provideNewsService(retrofit: Retrofit): NewsService = retrofit.create(NewsService::class.java)

    @Provides
    @Singleton
    fun provideChatService(retrofit: Retrofit): ChatService = retrofit.create(ChatService::class.java)

    @Provides
    @Singleton
    fun provideRentVehicleService(retrofit: Retrofit): RentVehicleService = retrofit.create(RentVehicleService::class.java)

    @Provides
    @Singleton
    fun providePartnerService(retrofit: Retrofit): PartnerService = retrofit.create(PartnerService::class.java)

    @Provides
    @Singleton
    fun providePartnerRentService(retrofit: Retrofit): PartnerRentService = retrofit.create(PartnerRentService::class.java)

    @Provides
    @Singleton
    fun providePartnerRewardService(retrofit: Retrofit): PartnerRewardService = retrofit.create(PartnerRewardService::class.java)

    @Provides
    @Singleton
    fun provideCustomerRentService(retrofit: Retrofit): CustomerRentService = retrofit.create(CustomerRentService::class.java)

    @Provides
    @Singleton
    fun provideImageLoader(@ApplicationContext context: Context): ImageLoader = ImageLoader.Builder(context).build()
}
