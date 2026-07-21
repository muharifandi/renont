package com.rentone.user.service

import android.app.Notification
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.graphics.Color
import android.media.AudioAttributes
import android.media.RingtoneManager
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.graphics.drawable.toBitmapOrNull
import coil.ImageLoader
import coil.request.ImageRequest
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import com.rentone.user.R
import com.rentone.user.api.service.CustomerService
import com.rentone.user.core.common.AppEvent
import com.rentone.user.core.common.AppEventBus
import com.rentone.user.core.common.Config
import com.rentone.user.domain.repository.UserRepository
import com.rentone.user.presentation.feature.customer.rentvehicle.transactiondetail.CustomerRentVehicleTransactionDetailActivity
import com.rentone.user.presentation.feature.news.detail.NewsDetailActivity
import com.rentone.user.presentation.feature.partner.rentvehicle.transactiondetail.PartnerRentVehicleTransactionDetailActivity
import com.rentone.user.presentation.feature.chat.conversation.ChatActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.cancel
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import timber.log.Timber
import javax.inject.Inject

@AndroidEntryPoint
class FirebaseCloudMessagingService : FirebaseMessagingService() {

    @Inject lateinit var customerService: CustomerService
    @Inject lateinit var userRepository: UserRepository
    @Inject lateinit var appEventBus: AppEventBus
    @Inject lateinit var imageLoader: ImageLoader

    private val serviceScope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    override fun onDestroy() {
        super.onDestroy()
        serviceScope.cancel()
    }

    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        Timber.d("From: %s", remoteMessage.from)

        if (remoteMessage.data.isNotEmpty()) {
            Timber.d("Message data payload: %s", remoteMessage.data)
        }

        val notification = remoteMessage.notification ?: return
        Timber.d("Message Notification: %s", notification.body)

        val dataType = remoteMessage.data["data_type"]?.lowercase()

        serviceScope.launch {
            val builder = buildNotification(notification)

            when (dataType) {
                "chat" -> handleChatMessage(remoteMessage, notification, builder)
                "partner_rent_vehicle_transaction" -> handlePartnerRentVehicleTransaction(remoteMessage, builder)
                "customer_rent_vehicle_transaction" -> handleCustomerRentVehicleTransaction(remoteMessage, builder)
                "news" -> handleNews(remoteMessage, builder)
                else -> startNotification(builder, notification)
            }
        }
    }

    private suspend fun handleChatMessage(
        remoteMessage: RemoteMessage,
        notification: RemoteMessage.Notification,
        builder: NotificationCompat.Builder
    ) {
        val data = remoteMessage.data
        appEventBus.emit(
            AppEvent.NewChatMessage(
                chatroomId = data["chatroom_id"]?.toIntOrNull() ?: 0,
                userType = data["user_type"]?.toIntOrNull() ?: 0,
                accountId = data["account_id"]?.toIntOrNull() ?: 0,
                attachmentType = data["attachment_type"]?.toIntOrNull() ?: 0,
                attachment = data["attachment"],
                message = data["message"],
                dateAdded = data["date_added"]
            )
        )

        val isPartner = data["to_partner"]?.toIntOrNull() == 1
        val chatroomId = data["chatroom_id"]?.toIntOrNull() ?: 0

        val resultIntent = Intent(applicationContext, ChatActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_CLEAR_TOP
            putExtra("is_partner", isPartner)
            putExtra("chatroom_id", chatroomId)
            putExtra("name", notification.title)
            putExtra("image", data["image"])
        }
        val pendingIntent = PendingIntent.getActivity(
            applicationContext, 0, resultIntent, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        builder.setContentIntent(pendingIntent)
        startNotification(builder, notification)
    }

    private suspend fun handlePartnerRentVehicleTransaction(
        remoteMessage: RemoteMessage,
        builder: NotificationCompat.Builder
    ) {
        val id = remoteMessage.data["id"]?.toIntOrNull() ?: 0
        appEventBus.emit(AppEvent.PartnerRentVehicleTransactionUpdated(id))

        val resultIntent = Intent(baseContext, PartnerRentVehicleTransactionDetailActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
            putExtra("id", id)
        }
        val pendingIntent = PendingIntent.getActivity(
            baseContext, 0, resultIntent, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        builder.setContentIntent(pendingIntent)
        startNotification(builder, remoteMessage.notification!!)
    }

    private suspend fun handleCustomerRentVehicleTransaction(
        remoteMessage: RemoteMessage,
        builder: NotificationCompat.Builder
    ) {
        val id = remoteMessage.data["id"]?.toIntOrNull() ?: 0
        appEventBus.emit(AppEvent.CustomerRentVehicleTransactionUpdated(id))

        val resultIntent = Intent(baseContext, CustomerRentVehicleTransactionDetailActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
            putExtra("id", id)
        }
        val pendingIntent = PendingIntent.getActivity(
            baseContext, 0, resultIntent, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        builder.setContentIntent(pendingIntent)
        startNotification(builder, remoteMessage.notification!!)
    }

    private fun handleNews(remoteMessage: RemoteMessage, builder: NotificationCompat.Builder) {
        val id = remoteMessage.data["id"]?.toIntOrNull() ?: 0
        val resultIntent = Intent(baseContext, NewsDetailActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
            putExtra("id", id)
        }
        val pendingIntent = PendingIntent.getActivity(
            baseContext, 0, resultIntent, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        builder.setContentIntent(pendingIntent)
        startNotification(builder, remoteMessage.notification!!)
    }

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        serviceScope.launch {
            userRepository.getUser().first() ?: return@launch
            runCatching { customerService.updateToken(mapOf("token" to token)) }
                .onFailure { Timber.w(it, "failed to update FCM token") }
        }
    }

    private fun startNotification(builder: NotificationCompat.Builder, notification: RemoteMessage.Notification) {
        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channelId = notification.channelId ?: Config.CHANNEL_ID
            val channel = NotificationChannel(channelId, applicationInfo.name, NotificationManager.IMPORTANCE_DEFAULT).apply {
                enableLights(true)
                lockscreenVisibility = Notification.VISIBILITY_PUBLIC
                lightColor = Color.RED
                enableVibration(true)
                vibrationPattern = longArrayOf(100, 200, 300, 400, 500, 400, 300, 200, 400)
                setSound(
                    RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION),
                    AudioAttributes.Builder()
                        .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                        .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                        .build()
                )
            }
            notificationManager.createNotificationChannel(channel)
        }
        notificationManager.notify(Config.NOTIFY_ID, builder.build())
    }

    private suspend fun buildNotification(notification: RemoteMessage.Notification): NotificationCompat.Builder {
        val channelId = notification.channelId ?: Config.CHANNEL_ID
        val bitmap = notification.imageUrl?.let { imageUrl ->
            val request = ImageRequest.Builder(applicationContext)
                .data(imageUrl.toString())
                .build()
            runCatching { imageLoader.execute(request).drawable?.toBitmapOrNull() }.getOrNull()
        }

        val builder = NotificationCompat.Builder(this, channelId)
            .setSmallIcon(R.mipmap.ic_launcher)
            .setContentTitle(notification.title)
            .setContentText(notification.body)
            .setAutoCancel(true)
            .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
            .setDefaults(Notification.DEFAULT_SOUND)
            .setPriority(NotificationCompat.PRIORITY_DEFAULT)
            .setChannelId(channelId)

        bitmap?.let {
            builder.setStyle(NotificationCompat.BigPictureStyle().bigPicture(it))
        }
        return builder
    }
}
