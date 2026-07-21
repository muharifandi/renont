package com.nusatim.sapiriku.presentation.feature.chat.roomlist.adapter
import android.text.format.DateUtils
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.view.isVisible
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.core.ui.LoadingFooterListAdapter
import com.nusatim.sapiriku.core.util.ViewUtils
import com.nusatim.sapiriku.databinding.ItemListChatroomBinding
import com.nusatim.sapiriku.domain.model.Chatroom
import java.text.SimpleDateFormat
import java.util.Locale

class ListChatroomAdapter(
    private val isPartner: Boolean,
    private val onItemClick: (Chatroom, imagePath: String?) -> Unit
) : LoadingFooterListAdapter<Chatroom>(DIFF_CALLBACK) {

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        ChatroomViewHolder(ItemListChatroomBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: Chatroom, position: Int) {
        (holder as ChatroomViewHolder).bind(item)
    }

    inner class ChatroomViewHolder(private val binding: ItemListChatroomBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(chatroom: Chatroom) {
            val imgPath = chatroom.imgProfile?.let {
                val base = if (isPartner) Config.BASE_CUSTOMER_IMAGE else Config.BASE_PARTNER_IMAGE
                "$base" + "thumb_" + it
            }

            if (imgPath != null) {
                binding.imageProfile.load(imgPath) { error(R.drawable.user_image) }
            } else {
                binding.imageProfile.setImageResource(R.drawable.user_image)
            }

            binding.txtName.text = chatroom.name
            binding.txtMessage.text = chatroom.message

            val chatDate = runCatching {
                SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault()).parse(chatroom.dateAdded.orEmpty())
            }.getOrNull()

            binding.txtDate.text = if (chatDate != null && DateUtils.isToday(chatDate.time)) {
                ViewUtils.mysqlDateToNormalDate(chatroom.dateAdded.orEmpty(), "yyyy-MM-dd HH:mm:ss", "HH:mm")
            } else {
                ViewUtils.mysqlDateToNormalDate(chatroom.dateAdded.orEmpty(), "yyyy-MM-dd HH:mm:ss", "dd/MM/yy")
            }

            binding.cvUnread.isVisible = chatroom.unread != 0
            binding.txtUnread.text = chatroom.unread.toString()

            binding.itemContainer.setOnClickListener { onItemClick(chatroom, imgPath) }
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<Chatroom>() {
            override fun areItemsTheSame(oldItem: Chatroom, newItem: Chatroom) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: Chatroom, newItem: Chatroom) = oldItem == newItem
        }
    }
}
